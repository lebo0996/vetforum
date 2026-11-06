<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Message;

class ChatController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $userIds = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->get()
            ->flatMap(fn($m) => [$m->sender_id, $m->receiver_id])
            ->unique()
            ->reject(fn($id) => $id === $user->id);

        // Cargamos los colegas y el último mensaje intercambiado con cada uno
        $conversations = User::whereIn('id', $userIds)->get()->map(function($colleague) use ($user) {
            $lastMessage = Message::where(function($q) use ($user, $colleague) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $colleague->id);
                })->orWhere(function($q) use ($user, $colleague) {
                    $q->where('sender_id', $colleague->id)->where('receiver_id', $user->id);
                })
                ->latest()
                ->first();

            $colleague->last_message = $lastMessage;
            return $colleague;
        })->sortByDesc(fn($c) => optional($c->last_message)->created_at);

        return view('chat.index', compact('conversations'));
    }

    public function show(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return redirect()->route('chat.index');
        }

        // VALIDACIÓN: ¿Se siguen mutuamente?
        $currentUserFollowsTarget = $currentUser->following()->where('following_id', $user->id)->exists();
        $targetFollowsCurrentUser = $user->following()->where('following_id', $currentUser->id)->exists();

        if (!$currentUserFollowsTarget || !$targetFollowsCurrentUser) {
            return redirect()->route('users.public', $user)->with('success', 'Para chatear con este colega, ambos deben seguirse mutuamente.');
        }

        // Marcar como leídos
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Cargar y mapear los mensajes para que Vue/Alpine los lea perfectos al inicio
        $messages = Message::where(function($q) use ($currentUser, $user) {
                $q->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
            })->orWhere(function($q) use ($currentUser, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
            })
            ->oldest()
            ->get()
            ->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'body' => $msg->body,
                    'image' => $msg->image,
                    'time_formatted' => $msg->created_at ? $msg->created_at->format('H:i') : ''
                ];
            });

        return view('chat.show', compact('user', 'messages'));
    }

    public function store(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Validamos si se siguen mutuamente usando a $user
        $currentUserFollowsTarget = $currentUser->following()->where('following_id', $user->id)->exists();
        $targetFollowsCurrentUser = $user->following()->where('following_id', $currentUser->id)->exists();

        if (!$currentUserFollowsTarget || !$targetFollowsCurrentUser) {
            return response()->json(['success' => false, 'message' => 'No se siguen mutuamente.'], 403);
        }

        $request->validate([
            'body' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:5120',
        ]);

        if (empty(trim($request->body ?? '')) && !$request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'El mensaje está vacío.'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat-images', 'public');
        }

        $message = Message::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $user->id,
            'body' => $request->body ?? '',
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'image' => $message->image,
                'time_formatted' => $message->created_at->format('H:i')
            ]
        ]);
    }

    /**
     * Devuelve en JSON los mensajes nuevos y el estado de actividad del usuario.
     */
    public function fetch(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        Cache::put('user-online-' . $currentUser->id, true, now()->addMinutes(2));

        $messages = Message::where(function($q) use ($currentUser, $user) {
                $q->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
            })->orWhere(function($q) use ($currentUser, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
            })
            ->oldest()
            ->get()
            ->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'body' => $msg->body,
                    'image' => $msg->image,
                    'time_formatted' => $msg->created_at->format('H:i')
                ];
            });

        $isTyping = Cache::has('user-typing-' . $user->id);
        $isOnline = Cache::has('user-online-' . $user->id);

        $statusText = $user->specialty?->name ?? 'Médico Veterinario';
        if ($isOnline) {
            $statusText = 'En línea';
        }

        return response()->json([
            'messages' => $messages,
            'is_typing' => $isTyping,
            'is_online' => $isOnline,
            'status_text' => $statusText
        ]);
    }

    public function typing(User $user)
    {
        Cache::put('user-typing-' . Auth::id(), true, now()->addSeconds(4));
        return response()->json(['status' => 'ok']);
    }
}
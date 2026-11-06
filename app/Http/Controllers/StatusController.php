<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Status;
use App\Models\StatusLike;
use App\Models\StatusComment;
use App\Models\User;
use App\Models\Story;
use App\Notifications\NewStatusCommentNotification;

class StatusController extends Controller
{
    public function index()
    {
        // 1. Traer los posts del muro asegurando que si hay repost, este tenga usuario válido
        $statuses = Status::with(['user', 'statusLikes', 'comments.user', 'repost.user', 'poll.options.votes'])
                          ->withCount('reposts')
                          ->latest()
                          ->get()
                          ->filter(function ($status) {
                              // Si es un repost pero el usuario original ya no existe, lo filtramos para que no rompa la vista
                              if ($status->repost_id && !$status->repost) {
                                  return false;
                              }
                              return true;
                          });

        $topColegas = User::withCount('statuses')
            ->orderBy('statuses_count', 'desc')
            ->take(5)
            ->get();

        $stories = collect(); 

        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            $followedUsersIds = $user->following()->pluck('users.id')->toArray();

            $stories = Story::with(['user', 'views', 'likes'])
                ->where('expires_at', '>', now())
                ->where(function($query) use ($followedUsersIds, $user) {
                    $query->whereIn('user_id', $followedUsersIds)
                          ->orWhere('user_id', $user->id);
                })
                ->orderBy('created_at', 'asc')
                ->get()
                ->groupBy('user_id'); 
        }

        return view('statuses.index', compact('statuses', 'topColegas', 'stories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'image' => 'nullable|image|max:2048',
            'poll_question' => 'nullable|string|max:255',
            'poll_options' => 'nullable|array|min:2',
            'poll_options.*' => 'required|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('statuses', 'public');
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        $status = $user->statuses()->create([
            'content' => $request->content,
            'image' => $imagePath,
        ]);

        // Guardar la encuesta si viene en la solicitud
        if ($request->filled('poll_question') && $request->filled('poll_options')) {
            $poll = $status->poll()->create([
                'question' => $request->poll_question
            ]);

            foreach ($request->poll_options as $optionText) {
                if (!empty(trim($optionText))) {
                    $poll->options()->create([
                        'option_text' => $optionText
                    ]);
                }
            }
        }

        $this->revisarInsigniasAutomaticas($user);

        return back()->with('success', '¡Nota publicada con éxito!');
    }

    public function repost(Request $request, Status $status)
    {
        $request->validate(['content' => 'nullable|string|max:250']);

        Status::create([
            'user_id' => Auth::id(),
            'content' => $request->content ?? 'Compartió esta nota con la comunidad.',
            'image' => null, 
            'repost_id' => $status->repost_id ?? $status->id,
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $this->revisarInsigniasAutomaticas($user);

        return back()->with('success', '¡Has republicado esta nota en tu muro!');
    }

    public function toggleLike(Status $status)
    {
        $existingLike = StatusLike::where('user_id', Auth::id())
                                    ->where('status_id', $status->id)
                                    ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            StatusLike::create([
                'user_id' => Auth::id(),
                'status_id' => $status->id,
            ]);

            // Enviar notificación al dueño del post (si no eres tú mismo)
            if ($status->user_id !== Auth::id()) {
                $status->user->notify(new \App\Notifications\GeneralNotification([
                    'message' => Auth::user()->name . ' le dio me gusta a tu publicación.',
                    'url' => route('statuses.index'),
                    'icon' => 'heart',
                    'color' => 'rose'
                ]));
            }
        }

        return back();
    }

    public function storeComment(Request $request, Status $status)
    {
        $data = $request->validate([
            'content' => 'required|string|max:250',
        ]);

        $comment = StatusComment::create([
            'user_id' => Auth::id(),
            'status_id' => $status->id,
            'content' => $data['content'],
        ]);

        if ($status->user_id !== Auth::id()) {
            $status->user->notify(new NewStatusCommentNotification($comment));
        }

        return back()->with('success', 'Comentario agregado.');
    }

    public function destroy(Status $status)
    {
        if ($status->user_id === Auth::id() || Auth::user()->role === 'admin') {
            if ($status->image) {
                Storage::disk('public')->delete($status->image);
            }
            $status->delete();
        }

        return back()->with('success', 'Publicación eliminada.');
    }
    
    public function update(Request $request, Status $status)
    {
        if ($status->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta nota.');
        }

        $request->validate(['content' => 'required|string|max:1000']);
        $status->update(['content' => $request->content]);

        return back()->with('success', 'Nota actualizada correctamente.');
    }

    private function revisarInsigniasAutomaticas(\App\Models\User $user)
    {
        $totalStatuses = $user->statuses()->count();

        $insigniasGanadas = \App\Models\Badge::where('requirement_type', 'statuses')
                                             ->where('requirement_goal', '<=', $totalStatuses)
                                             ->get();

        foreach ($insigniasGanadas as $badge) {
            if (!$user->badges->contains($badge->id)) {
                $user->badges()->attach($badge->id);
            }
        }
    }
}
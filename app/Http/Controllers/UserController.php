<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Specialty;

class UserController extends Controller
{
    public const SPECIALTIES = [
        'Bovinos y Rumiantes',
        'Equinos',
        'Pequeñas Especies (Caninos y Felinos)',
        'Porcinos',
        'Aves y Avicultura',
        'Fauna Silvestre y Exóticos',
        'Cirugía y Traumatología',
        'Reproducción y Genética',
        'Nutrición Animal',
        'Farmacología y Patología',
        'Estudiante de Veterinaria',
    ];

    public function directory(Request $request)
    {
        $search = $request->input('search');
        $specialty = $request->input('specialty');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        if ($specialty) {
            $query->where('specialty', $specialty);
        }

        $colleagues = $query->with('followers')->latest()->paginate(12);
        
        // <-- AHORA LAS LEEMOS DE LA BASE DE DATOS
        $specialties = Specialty::orderBy('name')->pluck('name'); 

        return view('users.directory', compact('colleagues', 'specialties', 'search', 'specialty'));
    }

    public function toggleFollow(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return back();
        }

        // Usamos toggle y guardamos el resultado para saber si se añadió o se quitó
        $changes = $currentUser->following()->toggle($user->id);

        // Si se acaba de agregar a la tabla pivot (es decir, lo empezó a seguir)
        if (!empty($changes['attached'])) {
            $user->notify(new \App\Notifications\GeneralNotification([
                'follower_name' => $currentUser->name,
                'follower_avatar' => $currentUser->avatar,
                'message' => 'ha comenzado a seguirte.',
                'url' => route('users.public', $currentUser),
                'icon' => 'user-plus',
                'color' => 'emerald'
            ]));
        }

        return back();
    }

    public function show(User $user)
    {
        $user->load([
            'statuses.user',
            'statuses.statusLikes',
            'statuses.comments.user',
            'followers',
            'following'
        ]);

        $totalLikesReceived = $user->statuses->sum(function ($status) {
            return $status->statusLikes->count();
        });

        $totalCommentsReceived = $user->statuses->sum(function ($status) {
            return $status->comments->count();
        });

        // 1. Verificamos si el usuario del perfil tiene historias activas hoy
        $tieneHistorias = \App\Models\Story::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->exists();

        $historiasNuevas = false;

        // 2. Si tiene historias y estamos logueados, revisamos si nos falta ver alguna
        if ($tieneHistorias && Auth::check()) {
            $historiasNuevas = \App\Models\Story::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->whereDoesntHave('views', function($q) {
                    $q->where('user_id', Auth::id());
                })->exists();
        }

        // Enviamos las nuevas variables a la vista
        return view('users.public-profile', compact('user', 'totalLikesReceived', 'totalCommentsReceived', 'tieneHistorias', 'historiasNuevas'));
    }
}
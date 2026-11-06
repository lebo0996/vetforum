<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewFollower; // <-- Importamos la notificación

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        if (Auth::id() === $user->id) {
            return back();
        }

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->following()->where('following_id', $user->id)->exists()) {
            // Dejar de seguir
            $currentUser->following()->detach($user->id);
            $message = "Has dejado de seguir a {$user->name}.";
        } else {
            // Seguir
            $currentUser->following()->attach($user->id);
            $message = "Ahora sigues a {$user->name}.";

            // ENVIAR LA NOTIFICACIÓN AL USUARIO QUE FUE SEGUIDO
            $user->notify(new NewFollower($currentUser));
        }

        return back()->with('success', $message);
    }
}
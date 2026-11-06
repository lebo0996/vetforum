<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. Baneo Permanente
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada permanentemente por infringir las normas de la comunidad.');
            }

            // 2. Suspensión Temporal
if ($user->suspended_until && now()->lessThan($user->suspended_until)) {
    $fecha = $user->suspended_until->format('d/m/Y h:i A');
    $dias = (int) ceil(now()->diffInHours($user->suspended_until) / 24);
    $tiempo = $dias > 1 ? "Te quedan {$dias} días de suspensión" : "Te queda 1 día de suspensión";
    Auth::logout();
    return redirect()->route('login')->with('error', "Tu cuenta está suspendida temporalmente. {$tiempo}, hasta el {$fecha}.");
}

        }

        return $next($request);
    }
}
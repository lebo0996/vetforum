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
                Auth::logout();
                return redirect()->route('login')->with('error', "Tu cuenta está suspendida temporalmente hasta el {$fecha}.");
            }
        }

        return $next($request);
    }
}
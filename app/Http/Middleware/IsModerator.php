<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsModerator
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'moderator'])) {
            return $next($request);
        }

        abort(403, 'No tienes permisos de moderación.');
    }
}
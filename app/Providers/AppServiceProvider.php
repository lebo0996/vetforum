<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // El superpoder: Solo si tu rol es exactamente 'admin'
        Gate::define('acceso-admin', function (User $user) {
            return $user->role === 'admin';
        });

        // Si en el futuro quieres agregar un Moderador, lo harías así:
        Gate::define('moderar-foro', function (User $user) {
            return in_array($user->role, ['admin', 'moderator']);
        });

        // ==========================================
        // COMPARTIR CONFIGURACIÓN GLOBAL (LOGO/FAVICON)
        // ==========================================
        try {
            View::share('settings', Setting::first());
        } catch (\Exception $e) {
            // Esto evita errores si ejecutas migraciones con la BD vacía
        }
    }
}
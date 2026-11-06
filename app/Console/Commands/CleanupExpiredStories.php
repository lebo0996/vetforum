<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredStories extends Command
{
    // El comando que usaremos en la terminal o en el Cron Job del servidor
    protected $signature = 'stories:cleanup';

    protected $description = 'Elimina las historias expiradas y sus archivos físicos del servidor';

    public function handle()
    {
        // 1. Buscar todas las historias donde 'expires_at' sea menor a la fecha/hora actual
        $expiredStories = Story::where('expires_at', '<', now())->get();

        $count = 0;
        foreach ($expiredStories as $story) {
            // 2. Borrar el archivo físico (imagen/video) del disco duro de Hostinger
            if (Storage::disk('public')->exists($story->media_path)) {
                Storage::disk('public')->delete($story->media_path);
            }
            
            // 3. Borrar el registro de la base de datos (por cascada, también borra sus 'views')
            $story->delete();
            $count++;
        }

        // Mensaje de éxito en consola
        $this->info("Limpieza completada. Se eliminaron {$count} historias expiradas del disco duro.");
    }
}
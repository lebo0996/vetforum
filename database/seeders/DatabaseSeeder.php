<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Configuración Base
        Setting::create([
            'site_name' => 'Veterinaria Ideal',
            'site_description' => 'Comparte conocimientos, discute casos clínicos y conecta con otros profesionales veterinarios en un espacio colaborativo.',
            'contact_email' => 'contacto@vetforum.com',
            'allow_registrations' => true,
        ]);

        // 2. Crear Categorías Iniciales
        $categories = [
            ['name' => 'Bovinos y Ganadería', 'desc' => 'Manejo de hato, reproducción, sanidad y producción pecuaria.', 'color' => '#10b981'],
            ['name' => 'Pequeñas Especies', 'desc' => 'Clínica, cirugía y medicina interna en perros y gatos.', 'color' => '#3b82f6'],
            ['name' => 'Farmacología y Terapéutica', 'desc' => 'Dosis, interacciones medicamentosas y protocolos de tratamiento.', 'color' => '#8b5cf6'],
            ['name' => 'Casos Clínicos', 'desc' => 'Discusión diagnóstica, radiografías, laboratorio y patología.', 'color' => '#f59e0b'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'description' => $cat['desc'],
                'color' => $cat['color'],
            ]);
        }
    }
}

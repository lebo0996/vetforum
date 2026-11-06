<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Servicios extra
            $table->boolean('offers_emergencies')->default(false); // ¿Atiende emergencias 24/7?
            $table->boolean('home_visits')->default(false); // ¿Visitas a domicilio o a rancho?

            // Ubicación Geográfica faltante
            $table->string('state')->nullable(); 
            $table->string('country')->default('México'); 
            
            // Redes Sociales y Web
            $table->string('website')->nullable(); 
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'offers_emergencies', 'home_visits',
                'state', 'country', 
                'website', 'facebook', 'instagram', 'linkedin'
            ]);
        });
    }
};
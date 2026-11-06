<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla de Insignias / Badges
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // Ej. "Colaborador Destacado", "Moderador"
            $table->string('color')->default('emerald'); // Ej. emerald, blue, amber, purple
            $table->string('icon')->default('award');    // Nombre del icono de Lucide (ej. award, shield, star)
            $table->timestamps();
        });

        // Tabla Pivote (Relación Muchos a Muchos entre Usuarios e Insignias)
        Schema::create('badge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_user');
        Schema::dropIfExists('badges');
    }
};
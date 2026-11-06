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
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        // Quién reporta
        $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
        // A quién están reportando
        $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete();
        
        // Motivo y detalles
        $table->string('reason'); // ej: Spam, Acoso, Comportamiento poco ético
        $table->text('description')->nullable();
        
        // Estado para tu panel de administración
        $table->enum('status', ['pendiente', 'en_revision', 'resuelto'])->default('pendiente');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Un usuario solo puede dar un like por estado
            $table->unique(['user_id', 'status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_likes');
    }
};
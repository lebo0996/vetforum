<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // El nombre de la especialidad no se puede repetir
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('specialties');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('badges', function (Blueprint $table) {
            // 'manual' será para las insignias únicas que tú asignas
            // Otras opciones serán: 'statuses' (notas), 'likes'
            $table->string('requirement_type')->default('manual')->after('icon');
            $table->integer('requirement_goal')->nullable()->default(0)->after('requirement_type');
        });
    }

    public function down()
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['requirement_type', 'requirement_goal']);
        });
    }
};
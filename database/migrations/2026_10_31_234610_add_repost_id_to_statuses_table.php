<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->foreignId('repost_id')->nullable()->constrained('statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropForeign(['repost_id']);
            $table->dropColumn('repost_id');
        });
    }
};
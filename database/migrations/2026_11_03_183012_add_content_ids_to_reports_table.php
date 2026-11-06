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
    Schema::table('reports', function (Blueprint $table) {
        // Añadimos las columnas como foráneas nulas. 
        // cascadeOnDelete() hace que si borras la publicación, sus reportes se borren solos para no dejar basura.
        $table->foreignId('status_id')->nullable()->constrained('statuses')->cascadeOnDelete()->after('reported_id');
        $table->foreignId('post_id')->nullable()->constrained('posts')->cascadeOnDelete()->after('status_id');
        $table->foreignId('comment_id')->nullable()->constrained('comments')->cascadeOnDelete()->after('post_id');
    });
}

public function down(): void
{
    Schema::table('reports', function (Blueprint $table) {
        $table->dropForeign(['status_id']);
        $table->dropForeign(['post_id']);
        $table->dropForeign(['comment_id']);
        $table->dropColumn(['status_id', 'post_id', 'comment_id']);
    });
}
};

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
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('site_name')->default('Foro Veterinario Pro');
        $table->text('site_description')->nullable();
        $table->string('contact_email')->default('contacto@vetforum.com');
        $table->boolean('allow_registrations')->default(true);
        $table->boolean('allow_guest_posting')->default(false);
        $table->boolean('posts_require_approval')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

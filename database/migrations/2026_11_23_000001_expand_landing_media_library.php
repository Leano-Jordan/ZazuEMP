<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_landing_settings', function (Blueprint $table) {
            $table->string('venue_image_path')->nullable();
            $table->string('decor_image_path')->nullable();
        });

        Schema::create('platform_landing_media', function (Blueprint $table) {
            $table->id();
            $table->string('label', 120);
            $table->string('path')->unique();
            $table->string('mime_type', 80);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_landing_media');

        Schema::table('platform_landing_settings', function (Blueprint $table) {
            $table->dropColumn(['venue_image_path', 'decor_image_path']);
        });
    }
};

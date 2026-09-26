<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('status');
            $table->string('dashboard_image_path')->nullable()->after('logo_path');
            $table->string('wallpaper_path')->nullable()->after('dashboard_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'dashboard_image_path', 'wallpaper_path']);
        });
    }
};

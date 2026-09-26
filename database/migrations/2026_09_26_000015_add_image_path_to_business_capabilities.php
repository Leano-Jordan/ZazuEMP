<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_capabilities')) {
            return;
        }

        Schema::table('business_capabilities', function (Blueprint $table) {
            if (!Schema::hasColumn('business_capabilities', 'image_path')) {
                $table->string('image_path')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('business_capabilities') || !Schema::hasColumn('business_capabilities', 'image_path')) {
            return;
        }

        Schema::table('business_capabilities', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
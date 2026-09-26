<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->timestamp('catalogue_setup_skipped_at')->nullable()->after('catalogue_setup_completed_at');
            $table->timestamp('business_setup_skipped_at')->nullable()->after('business_setup_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['catalogue_setup_skipped_at', 'business_setup_skipped_at']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('email')->nullable()->after('currency');
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
            $table->string('website')->nullable()->after('address');
            $table->string('tax_number')->nullable()->after('website');
            $table->timestamp('catalogue_setup_completed_at')->nullable()->after('tax_number');
            $table->timestamp('business_setup_completed_at')->nullable()->after('catalogue_setup_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'phone',
                'address',
                'website',
                'tax_number',
                'catalogue_setup_completed_at',
                'business_setup_completed_at',
            ]);
        });
    }
};
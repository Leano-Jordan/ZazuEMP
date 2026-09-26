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
            if (!Schema::hasColumn('business_capabilities', 'default_price')) {
                $table->decimal('default_price', 12, 2)->nullable()->after('pricing_basis');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('business_capabilities') || !Schema::hasColumn('business_capabilities', 'default_price')) {
            return;
        }

        Schema::table('business_capabilities', function (Blueprint $table) {
            $table->dropColumn('default_price');
        });
    }
};
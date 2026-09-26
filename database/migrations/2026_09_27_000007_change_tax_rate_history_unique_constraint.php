<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_rates', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'code']);
            $table->unique(['business_id', 'code', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::table('tax_rates', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'code', 'effective_from']);
            $table->unique(['business_id', 'code']);
        });
    }
};
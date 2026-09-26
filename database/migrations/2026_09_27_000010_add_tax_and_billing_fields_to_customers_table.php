<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('registration_number')->nullable()->after('legal_name');
            $table->string('tax_number')->nullable()->after('registration_number');
            $table->string('vat_number')->nullable()->after('tax_number');
            $table->text('billing_address')->nullable()->after('vat_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'registration_number',
                'tax_number',
                'vat_number',
                'billing_address',
            ]);
        });
    }
};
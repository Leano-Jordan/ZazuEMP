<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('business_legal_name')->nullable()->after('number');
            $table->string('business_trading_name')->nullable()->after('business_legal_name');
            $table->text('business_address')->nullable()->after('business_trading_name');
            $table->string('business_email')->nullable()->after('business_address');
            $table->string('business_phone')->nullable()->after('business_email');
            $table->string('business_tax_number')->nullable()->after('business_phone');
            $table->string('business_vat_number')->nullable()->after('business_tax_number');
            $table->string('customer_name')->nullable()->after('business_vat_number');
            $table->text('customer_address')->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_address');
            $table->string('customer_phone')->nullable()->after('customer_email');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'business_legal_name',
                'business_trading_name',
                'business_address',
                'business_email',
                'business_phone',
                'business_tax_number',
                'business_vat_number',
                'customer_name',
                'customer_address',
                'customer_email',
                'customer_phone',
            ]);
        });
    }
};
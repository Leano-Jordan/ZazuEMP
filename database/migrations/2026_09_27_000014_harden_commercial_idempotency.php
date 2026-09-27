<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable()->after('number');
            $table->unique(['business_id', 'idempotency_key']);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable()->after('invoice_id');
            $table->unique(['business_id', 'idempotency_key']);
        });

        Schema::table('finance_expenses', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable()->after('business_id');
            $table->unique(['business_id', 'idempotency_key']);
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable()->after('inventory_item_id');
            $table->unique(['business_id', 'idempotency_key']);
            $table->unique(['business_id', 'purchase_order_item_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropUnique('inventory_movements_business_id_idempotency_key_unique');
            $table->dropUnique('inventory_movements_business_id_purchase_order_item_id_type_unique');
            $table->dropColumn('idempotency_key');
        });

        Schema::table('finance_expenses', function (Blueprint $table): void {
            $table->dropUnique('finance_expenses_business_id_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_business_id_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_business_id_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
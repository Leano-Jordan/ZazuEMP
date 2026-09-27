<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->unique(['business_id', 'idempotency_key'], 'invoices_business_idempotency_unique');
            $table->unique(['business_id', 'number'], 'invoices_business_number_unique');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->unique(['business_id', 'idempotency_key'], 'payments_business_idempotency_unique');
        });

        Schema::table('finance_expenses', function (Blueprint $table): void {
            $table->unique(['business_id', 'idempotency_key'], 'finance_expenses_business_idempotency_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->unique(['business_id', 'idempotency_key'], 'purchase_orders_business_idempotency_unique');
            $table->unique(['business_id', 'reference'], 'purchase_orders_business_reference_unique');
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->unique(['business_id', 'idempotency_key'], 'inventory_movements_business_idempotency_unique');
            $table->unique(
                ['business_id', 'purchase_order_id', 'purchase_order_item_id', 'type'],
                'inventory_po_receipt_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropUnique('inventory_po_receipt_unique');
            $table->dropUnique('inventory_movements_business_idempotency_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropUnique('purchase_orders_business_reference_unique');
            $table->dropUnique('purchase_orders_business_idempotency_unique');
        });

        Schema::table('finance_expenses', function (Blueprint $table): void {
            $table->dropUnique('finance_expenses_business_idempotency_unique');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_business_idempotency_unique');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_business_number_unique');
            $table->dropUnique('invoices_business_idempotency_unique');
        });
    }
};

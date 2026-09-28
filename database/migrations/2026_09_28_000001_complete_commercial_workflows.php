<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->uuid('last_receipt_idempotency_key')->nullable()->after('idempotency_key');
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropUnique('inventory_po_receipt_unique');
        });

        Schema::table('quote_versions', function (Blueprint $table): void {
            $table->decimal('deposit_percent', 5, 2)->default(0)->after('total');
            $table->decimal('deposit_amount', 12, 2)->default(0)->after('deposit_percent');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('type')->default('payment')->after('invoice_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->decimal('received_quantity', 12, 2)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->unique(
                ['business_id', 'purchase_order_id', 'purchase_order_item_id', 'type'],
                'inventory_po_receipt_unique'
            );
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropColumn('last_receipt_idempotency_key');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropColumn('received_quantity');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('type');
        });

        Schema::table('quote_versions', function (Blueprint $table): void {
            $table->dropColumn(['deposit_percent', 'deposit_amount']);
        });
    }
};

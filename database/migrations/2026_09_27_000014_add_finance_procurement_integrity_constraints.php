<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotency indexes for invoices, payments, expenses and inventory movements
        // are created by the earlier hardening migration. Do not create them twice.
        Schema::table('invoices', function (Blueprint $table): void {
            $table->unique(['business_id', 'number'], 'invoices_business_number_unique');
        });

        // Purchase-order idempotency is added by 000016, after this migration.
        Schema::table('inventory_movements', function (Blueprint $table): void {
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
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_business_number_unique');
        });
    }
};
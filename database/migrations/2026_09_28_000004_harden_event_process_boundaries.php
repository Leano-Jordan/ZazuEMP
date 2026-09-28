<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreignId('event_id')
                ->nullable()
                ->after('business_id')
                ->index('purchase_orders_business_event_index');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->unique(['business_id', 'id'], 'invoices_business_id_id_unique');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->unique(['business_id', 'id'], 'purchase_order_items_business_id_id_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'event_id'],
                'purchase_orders_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->restrictOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'event_id'],
                'invoices_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->restrictOnDelete();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'invoice_id'],
                'payments_business_invoice_fk'
            )->references(['business_id', 'id'])->on('invoices')->restrictOnDelete();

            $table->foreign(
                ['business_id', 'event_id'],
                'payments_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->restrictOnDelete();
        });

        Schema::table('finance_expenses', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'event_id'],
                'finance_expenses_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->restrictOnDelete();

            $table->foreign(
                ['business_id', 'supplier_id'],
                'finance_expenses_business_supplier_fk'
            )->references(['business_id', 'id'])->on('suppliers')->restrictOnDelete();

            $table->foreign(
                ['business_id', 'purchase_order_id'],
                'finance_expenses_business_purchase_order_fk'
            )->references(['business_id', 'id'])->on('purchase_orders')->restrictOnDelete();
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'event_id'],
                'inventory_movements_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->restrictOnDelete();

            $table->foreign(
                ['business_id', 'purchase_order_id'],
                'inventory_movements_business_purchase_order_fk'
            )->references(['business_id', 'id'])->on('purchase_orders')->restrictOnDelete();

            $table->foreign(
                ['business_id', 'purchase_order_item_id'],
                'inventory_movements_business_purchase_order_item_fk'
            )->references(['business_id', 'id'])->on('purchase_order_items')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropForeign('inventory_movements_business_purchase_order_item_fk');
            $table->dropForeign('inventory_movements_business_purchase_order_fk');
            $table->dropForeign('inventory_movements_business_event_fk');
        });

        Schema::table('finance_expenses', function (Blueprint $table): void {
            $table->dropForeign('finance_expenses_business_purchase_order_fk');
            $table->dropForeign('finance_expenses_business_supplier_fk');
            $table->dropForeign('finance_expenses_business_event_fk');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropForeign('payments_business_event_fk');
            $table->dropForeign('payments_business_invoice_fk');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropForeign('invoices_business_event_fk');
            $table->dropUnique('invoices_business_id_id_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropForeign('purchase_orders_business_event_fk');
            $table->dropIndex('purchase_orders_business_event_index');
            $table->dropColumn('event_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropUnique('purchase_order_items_business_id_id_unique');
        });
    }
};

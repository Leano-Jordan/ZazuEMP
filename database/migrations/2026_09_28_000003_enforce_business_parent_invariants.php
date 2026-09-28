<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Parent composite keys make the business_id + parent_id relationship
        // enforceable at database level instead of relying only on controllers.
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->unique(['business_id', 'id'], 'suppliers_business_id_id_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->unique(['business_id', 'id'], 'purchase_orders_business_id_id_unique');
        });

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->unique(['business_id', 'id'], 'inventory_items_business_id_id_unique');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->unique(['business_id', 'id'], 'events_business_id_id_unique');
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->unique(['business_id', 'id'], 'assets_business_id_id_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'supplier_id'],
                'purchase_orders_business_supplier_fk'
            )->references(['business_id', 'id'])->on('suppliers')->restrictOnDelete();
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'purchase_order_id'],
                'purchase_order_items_business_order_fk'
            )->references(['business_id', 'id'])->on('purchase_orders')->cascadeOnDelete();
        });

        Schema::table('purchase_order_receipts', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'purchase_order_id'],
                'purchase_order_receipts_business_order_fk'
            )->references(['business_id', 'id'])->on('purchase_orders')->cascadeOnDelete();
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'inventory_item_id'],
                'inventory_movements_business_item_fk'
            )->references(['business_id', 'id'])->on('inventory_items')->cascadeOnDelete();
        });

        Schema::table('asset_allocations', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'asset_id'],
                'asset_allocations_business_asset_fk'
            )->references(['business_id', 'id'])->on('assets')->cascadeOnDelete();

            $table->foreign(
                ['business_id', 'event_id'],
                'asset_allocations_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->cascadeOnDelete();
        });

        Schema::table('event_costs', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'event_id'],
                'event_costs_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->cascadeOnDelete();
        });

        Schema::table('event_preparation_items', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'event_id'],
                'event_preparation_items_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->cascadeOnDelete();
        });

        Schema::table('event_attachments', function (Blueprint $table): void {
            $table->foreign(
                ['business_id', 'event_id'],
                'event_attachments_business_event_fk'
            )->references(['business_id', 'id'])->on('events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_attachments', function (Blueprint $table): void {
            $table->dropForeign('event_attachments_business_event_fk');
        });

        Schema::table('event_preparation_items', function (Blueprint $table): void {
            $table->dropForeign('event_preparation_items_business_event_fk');
        });

        Schema::table('event_costs', function (Blueprint $table): void {
            $table->dropForeign('event_costs_business_event_fk');
        });

        Schema::table('asset_allocations', function (Blueprint $table): void {
            $table->dropForeign('asset_allocations_business_event_fk');
            $table->dropForeign('asset_allocations_business_asset_fk');
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropForeign('inventory_movements_business_item_fk');
        });

        Schema::table('purchase_order_receipts', function (Blueprint $table): void {
            $table->dropForeign('purchase_order_receipts_business_order_fk');
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropForeign('purchase_order_items_business_order_fk');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropForeign('purchase_orders_business_supplier_fk');
            $table->dropUnique('purchase_orders_business_id_id_unique');
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->dropUnique('assets_business_id_id_unique');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropUnique('events_business_id_id_unique');
        });

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropUnique('inventory_items_business_id_id_unique');
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropUnique('suppliers_business_id_id_unique');
        });
    }
};

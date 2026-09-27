<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('purchase_order_items')
                ->nullOnDelete();

            $table->index(['business_id', 'purchase_order_item_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropIndex(['business_id', 'purchase_order_item_id', 'type']);
            $table->dropColumn('purchase_order_item_id');
        });
    }
};

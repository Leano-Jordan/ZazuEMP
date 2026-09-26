<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('capability_id')->nullable()->constrained('business_capabilities')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['business_id', 'purchase_order_id']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('capability_id')->nullable()->constrained('business_capabilities')->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->string('unit')->default('unit');
            $table->decimal('reorder_level', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['business_id', 'name']);
            $table->unique(['business_id', 'sku']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->date('movement_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'inventory_item_id', 'movement_date']);
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('capability_id')->nullable()->constrained('business_capabilities')->nullOnDelete();
            $table->string('asset_tag');
            $table->string('name');
            $table->string('status')->default('available');
            $table->string('condition')->default('good');
            $table->string('location')->nullable();
            $table->date('acquired_at')->nullable();
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->char('currency', 3)->default('ZAR');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'asset_tag']);
            $table->index(['business_id', 'status']);
        });

        Schema::create('asset_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->date('allocated_from');
            $table->date('allocated_until')->nullable();
            $table->string('status')->default('allocated');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'asset_id', 'status']);
            $table->index(['business_id', 'event_id']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number');
            $table->string('status')->default('draft');
            $table->char('currency', 3);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'number']);
            $table->index(['business_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->string('method')->default('bank_transfer');
            $table->string('reference')->nullable();
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'paid_at']);
        });

        Schema::create('finance_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->date('expense_date');
            $table->string('status')->default('unpaid');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('asset_allocations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('purchase_order_items');
    }
};

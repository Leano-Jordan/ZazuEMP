<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->uuid('idempotency_key');
            $table->timestamps();

            $table->unique(
                ['business_id', 'purchase_order_id', 'idempotency_key'],
                'purchase_order_receipts_idempotency_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receipts');
    }
};

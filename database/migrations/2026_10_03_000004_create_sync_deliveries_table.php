<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sync_mutation_id')->constrained('sync_mutations')->cascadeOnDelete();
            $table->foreignId('destination_device_id')->constrained('sync_devices')->cascadeOnDelete();
            $table->string('stream', 64)->default('business');
            $table->unsignedBigInteger('delivery_sequence');
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['destination_device_id', 'stream', 'delivery_sequence']);
            $table->unique(['sync_mutation_id', 'destination_device_id']);
            $table->index(['business_id', 'destination_device_id', 'stream', 'status']);
            $table->index(['sync_mutation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_deliveries');
    }
};

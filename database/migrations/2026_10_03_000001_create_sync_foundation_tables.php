<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->uuid('installation_id')->unique();
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'last_seen_at']);
        });

        Schema::create('sync_mutations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sync_device_id')->constrained('sync_devices')->cascadeOnDelete();
            $table->uuid('mutation_id')->unique();
            $table->string('entity_type', 255);
            $table->string('entity_id', 255);
            $table->string('operation', 32);
            $table->string('status', 32)->default('pending');
            $table->json('payload')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status', 'occurred_at']);
            $table->index(['business_id', 'entity_type', 'entity_id']);
            $table->index(['sync_device_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_mutations');
        Schema::dropIfExists('sync_devices');
    }
};

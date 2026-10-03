<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_entity_identities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 64);
            $table->uuid('entity_uuid');
            $table->string('record_type', 191);
            $table->unsignedBigInteger('record_id');
            $table->timestamps();

            $table->unique(['business_id', 'entity_uuid'], 'sync_entity_identity_uuid_unique');
            $table->unique(['business_id', 'record_type', 'record_id'], 'sync_entity_identity_record_unique');
            $table->index(['business_id', 'entity_type', 'entity_uuid'], 'sync_entity_identity_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_entity_identities');
    }
};

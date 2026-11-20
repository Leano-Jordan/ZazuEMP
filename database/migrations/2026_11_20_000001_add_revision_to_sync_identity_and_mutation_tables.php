<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_entity_identities', function (Blueprint $table): void {
            $table->unsignedBigInteger('revision')->default(2)->after('record_id');
        });

        Schema::table('sync_mutations', function (Blueprint $table): void {
            $table->unsignedBigInteger('result_revision')->nullable()->after('applied_at');
            $table->uuid('superseded_by_mutation_id')->nullable()->after('result_revision');
            $table->index(['business_id', 'sync_device_id', 'superseded_by_mutation_id'], 'sync_mutation_supersession_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('sync_mutations', function (Blueprint $table): void {
            $table->dropIndex('sync_mutation_supersession_lookup');
            $table->dropColumn(['result_revision', 'superseded_by_mutation_id']);
        });

        Schema::table('sync_entity_identities', function (Blueprint $table): void {
            $table->dropColumn('revision');
        });
    }
};

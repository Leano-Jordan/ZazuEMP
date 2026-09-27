<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reconcile the live events table with the Event model contract.
     *
     * This migration is intentionally additive. It repairs databases where the
     * migration history says the workflow migrations ran but the physical schema
     * is missing one or more columns.
     */
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        if (!Schema::hasColumn('events', 'business_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('business_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('events', 'customer_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('business_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('events', 'event_day_contact_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('event_day_contact_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('customer_contacts')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('events', 'event_night_contact_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('event_night_contact_id')
                    ->nullable()
                    ->after('event_day_contact_id')
                    ->constrained('customer_contacts')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('events', 'deleted_at')) {
            Schema::table('events', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Deliberately non-destructive. This migration repairs live schemas and
        // must not remove columns that may be required by the current model.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            return;
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

        if (Schema::hasColumn('events', 'customer_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->index(['customer_id', 'deleted_at']);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        if (Schema::hasColumn('events', 'customer_id') && Schema::hasColumn('events', 'deleted_at')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropIndex(['customer_id', 'deleted_at']);
            });
        }

        if (Schema::hasColumn('events', 'event_night_contact_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropForeign(['event_night_contact_id']);
                $table->dropColumn('event_night_contact_id');
            });
        }

        if (Schema::hasColumn('events', 'deleted_at')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

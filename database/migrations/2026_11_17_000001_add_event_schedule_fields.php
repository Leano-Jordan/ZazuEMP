<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('events')) {
            return;
        }

        Schema::table('events', function (Blueprint $table): void {
            if (! Schema::hasColumn('events', 'event_end_date')) {
                $table->date('event_end_date')->nullable()->after('event_date');
            }
            if (! Schema::hasColumn('events', 'event_start_time')) {
                $table->time('event_start_time')->nullable()->after('event_end_date');
            }
            if (! Schema::hasColumn('events', 'event_end_time')) {
                $table->time('event_end_time')->nullable()->after('event_start_time');
            }
            if (! Schema::hasColumn('events', 'setup_date')) {
                $table->date('setup_date')->nullable()->after('event_end_time');
            }
            if (! Schema::hasColumn('events', 'collection_date')) {
                $table->date('collection_date')->nullable()->after('setup_date');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: schedule data must survive rollback of application code.
    }
};

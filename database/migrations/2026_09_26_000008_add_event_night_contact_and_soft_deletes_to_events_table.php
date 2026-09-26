<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('event_night_contact_id')
                ->nullable()
                ->after('event_day_contact_id')
                ->constrained('customer_contacts')
                ->nullOnDelete();

            $table->softDeletes();

            $table->index(['customer_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['event_night_contact_id']);
            $table->dropIndex(['customer_id', 'deleted_at']);
            $table->dropColumn('event_night_contact_id');
            $table->dropSoftDeletes();
        });
    }
};

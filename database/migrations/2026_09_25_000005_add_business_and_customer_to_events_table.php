<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('business_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->after('business_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('event_day_contact_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('customer_contacts')
                ->nullOnDelete();

            $table->index(['business_id', 'event_date']);
            $table->index(['customer_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['event_day_contact_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['business_id']);
            $table->dropIndex(['business_id', 'event_date']);
            $table->dropIndex(['customer_id', 'event_date']);
            $table->dropColumn([
                'event_day_contact_id',
                'customer_id',
                'business_id',
            ]);
        });
    }
};
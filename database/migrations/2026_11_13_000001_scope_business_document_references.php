<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropUnique(['reference']);
            $table->unique(['business_id', 'reference'], 'events_business_reference_unique');
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropUnique(['reference']);
            $table->unique(['event_id', 'reference'], 'quotes_event_reference_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropUnique(['reference']);
            $table->unique(['business_id', 'reference'], 'purchase_orders_business_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropUnique('purchase_orders_business_reference_unique');
            $table->unique('reference');
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropUnique('quotes_event_reference_unique');
            $table->unique('reference');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropUnique('events_business_reference_unique');
            $table->unique('reference');
        });
    }
};

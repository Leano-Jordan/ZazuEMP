<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('route_label')->default('Route A');
            $table->char('currency', 3)->default('ZAR');
            $table->string('provider')->default('manual');
            $table->string('origin');
            $table->string('destination');
            $table->decimal('distance_km', 10, 2);
            $table->unsignedInteger('travel_time_minutes')->nullable();
            $table->decimal('fuel_price_per_litre', 10, 2);
            $table->decimal('vehicle_consumption_l_per_100km', 10, 2);
            $table->boolean('round_trip')->default(true);
            $table->decimal('customer_rate_per_km', 10, 2);
            $table->decimal('total_distance_km', 10, 2);
            $table->decimal('fuel_litres', 10, 3);
            $table->decimal('fuel_cost', 12, 2);
            $table->decimal('customer_charge', 12, 2);
            $table->text('notes')->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_costs');
    }
};
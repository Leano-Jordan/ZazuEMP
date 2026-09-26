<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\TravelCost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelCostWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_cost_is_calculated_and_saved_as_historical_evidence(): void
    {
        $customer = Customer::create(['name' => 'Travel Customer']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-TRAVEL-001',
            'name' => 'Travel Test Event',
            'event_date' => '2026-10-20',
            'status' => 'draft',
        ]);

        $response = $this->post(route('work.travel.store', $event), [
            'route_label' => 'Route A',
            'currency' => 'zar',
            'provider' => 'manual',
            'origin' => 'Pretoria',
            'destination' => 'Centurion',
            'distance_km' => 50,
            'travel_time_minutes' => 45,
            'fuel_price_per_litre' => 24.50,
            'vehicle_consumption_l_per_100km' => 10,
            'round_trip' => '1',
            'customer_rate_per_km' => 14,
            'notes' => 'Manual route evidence',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('work.travel.index', $event));
        $response->assertSessionHas('success', 'Travel calculation saved.');
        $travel = TravelCost::query()->firstOrFail();

        $this->assertSame('ZAR', $travel->currency);
        $this->assertSame('100.00', (string) $travel->total_distance_km);
        $this->assertSame('10.000', (string) $travel->fuel_litres);
        $this->assertSame('245.00', (string) $travel->fuel_cost);
        $this->assertSame('1400.00', (string) $travel->customer_charge);
        $this->assertSame(14.0, (float) $travel->calculation_snapshot['customer_rate_per_km']);
    }

    public function test_one_way_travel_cost_does_not_double_the_distance(): void
    {
        $event = Event::create([
            'reference' => 'ZAZ-TRAVEL-002',
            'name' => 'One Way Travel Event',
            'event_date' => '2026-10-21',
            'status' => 'draft',
        ]);

        $response = $this->post(route('work.travel.store', $event), [
            'route_label' => 'Route B',
            'currency' => 'ZAR',
            'provider' => 'manual',
            'origin' => 'Pretoria',
            'destination' => 'Midrand',
            'distance_km' => 40,
            'fuel_price_per_litre' => 24,
            'vehicle_consumption_l_per_100km' => 8,
            'customer_rate_per_km' => 10,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('work.travel.index', $event));
        $response->assertSessionHas('success', 'Travel calculation saved.');
        $travel = TravelCost::query()->firstOrFail();

        $this->assertFalse($travel->round_trip);
        $this->assertSame('40.00', (string) $travel->total_distance_km);
        $this->assertSame('3.200', (string) $travel->fuel_litres);
        $this->assertSame('76.80', (string) $travel->fuel_cost);
        $this->assertSame('400.00', (string) $travel->customer_charge);
    }
}

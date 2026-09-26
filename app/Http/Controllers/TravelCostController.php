<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TravelCost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TravelCostController extends Controller
{
    public function index(Event $event): View
    {
        $event->load('customer');
        $travelCosts = $event->travelCosts()->latest()->get();

        return view('travel.index', compact('event', 'travelCosts'));
    }

    public function create(Event $event): View
    {
        $event->load('customer');

        return view('travel.create', compact('event'));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'route_label' => ['required', 'string', 'max:100'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'provider' => ['required', 'string', 'max:100'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'distance_km' => ['required', 'numeric', 'gt:0'],
            'travel_time_minutes' => ['nullable', 'integer', 'min:0'],
            'fuel_price_per_litre' => ['required', 'numeric', 'gt:0'],
            'vehicle_consumption_l_per_100km' => ['required', 'numeric', 'gt:0'],
            'round_trip' => ['boolean'],
            'customer_rate_per_km' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $roundTrip = $request->boolean('round_trip');
        $distance = (float) $validated['distance_km'];
        $fuelPrice = (float) $validated['fuel_price_per_litre'];
        $consumption = (float) $validated['vehicle_consumption_l_per_100km'];
        $customerRate = (float) $validated['customer_rate_per_km'];

        $totalDistance = round($distance * ($roundTrip ? 2 : 1), 2);
        $fuelLitres = round(($totalDistance * $consumption) / 100, 3);
        $fuelCost = round($fuelLitres * $fuelPrice, 2);
        $customerCharge = round($totalDistance * $customerRate, 2);

        $travelCost = DB::transaction(function () use (
            $validated,
            $event,
            $roundTrip,
            $totalDistance,
            $fuelLitres,
            $fuelCost,
            $customerCharge
        ): TravelCost {
            return $event->travelCosts()->create([
                'route_label' => $validated['route_label'],
                'currency' => strtoupper($validated['currency']),
                'provider' => $validated['provider'],
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'distance_km' => $validated['distance_km'],
                'travel_time_minutes' => $validated['travel_time_minutes'] ?? null,
                'fuel_price_per_litre' => $validated['fuel_price_per_litre'],
                'vehicle_consumption_l_per_100km' => $validated['vehicle_consumption_l_per_100km'],
                'round_trip' => $roundTrip,
                'customer_rate_per_km' => $validated['customer_rate_per_km'],
                'total_distance_km' => $totalDistance,
                'fuel_litres' => $fuelLitres,
                'fuel_cost' => $fuelCost,
                'customer_charge' => $customerCharge,
                'notes' => $validated['notes'] ?? null,
                'calculation_snapshot' => [
                    'route_label' => $validated['route_label'],
                    'currency' => strtoupper($validated['currency']),
                    'provider' => $validated['provider'],
                    'origin' => $validated['origin'],
                    'destination' => $validated['destination'],
                    'distance_km' => $distance,
                    'travel_time_minutes' => $validated['travel_time_minutes'] ?? null,
                    'fuel_price_per_litre' => (float) $validated['fuel_price_per_litre'],
                    'vehicle_consumption_l_per_100km' => (float) $validated['vehicle_consumption_l_per_100km'],
                    'round_trip' => $roundTrip,
                    'customer_rate_per_km' => (float) $validated['customer_rate_per_km'],
                    'total_distance_km' => $totalDistance,
                    'fuel_litres' => $fuelLitres,
                    'fuel_cost' => $fuelCost,
                    'customer_charge' => $customerCharge,
                ],
            ]);
        });

        return redirect()
            ->route('work.travel.index', $event)
            ->with('success', 'Travel calculation saved.');
    }
}

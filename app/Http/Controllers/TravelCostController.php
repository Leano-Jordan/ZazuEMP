<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TravelCost;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TravelCostController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);
        $event->load('customer');
        $travelCosts = $event->travelCosts()->latest()->get();

        return view('travel.index', compact('event', 'travelCosts'));
    }

    public function create(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);
        $event->load('customer');

        return view('travel.create', [
            'event' => $event,
            'currencies' => config('zazu.currencies'),
            'defaultCurrency' => app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR',
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->ensureBusiness($event, $request);
        $request->merge(['currency' => strtoupper((string) $request->input('currency'))]);
        abort_if($event->isClosed(), 422, 'Closed work cannot receive new travel records.');

        $validated = $request->validate([
            'route_label' => ['required', 'string', 'max:100'],
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
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
        $calculation = TravelCost::calculate(
            (string) $validated['distance_km'],
            (string) $validated['fuel_price_per_litre'],
            (string) $validated['vehicle_consumption_l_per_100km'],
            $roundTrip,
            (string) $validated['customer_rate_per_km'],
        );

        $travelCost = DB::transaction(function () use ($validated, $event, $roundTrip, $calculation): TravelCost {
            $lockedEvent = Event::query()
                ->whereKey($event->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($lockedEvent->isClosed(), 422, 'Closed work cannot receive new travel records.');

            return TravelCost::query()->create([
                'event_id' => $lockedEvent->id,
                'route_label' => trim($validated['route_label']),
                'currency' => strtoupper($validated['currency']),
                'provider' => 'manual',
                'origin' => trim($validated['origin']),
                'destination' => trim($validated['destination']),
                'distance_km' => $calculation['distance_km'],
                'travel_time_minutes' => $validated['travel_time_minutes'] ?? null,
                'fuel_price_per_litre' => $validated['fuel_price_per_litre'],
                'vehicle_consumption_l_per_100km' => $validated['vehicle_consumption_l_per_100km'],
                'round_trip' => $roundTrip,
                'customer_rate_per_km' => $validated['customer_rate_per_km'],
                'total_distance_km' => $calculation['total_distance_km'],
                'fuel_litres' => $calculation['fuel_litres'],
                'fuel_cost' => $calculation['fuel_cost'],
                'customer_charge' => $calculation['customer_charge'],
                'notes' => $validated['notes'] ?? null,
                'calculation_snapshot' => [
                    'route_label' => trim($validated['route_label']),
                    'currency' => strtoupper($validated['currency']),
                    'provider' => 'manual',
                    'origin' => trim($validated['origin']),
                    'destination' => trim($validated['destination']),
                    'distance_km' => $calculation['distance_km'],
                    'travel_time_minutes' => $validated['travel_time_minutes'] ?? null,
                    'fuel_price_per_litre' => (string) $validated['fuel_price_per_litre'],
                    'vehicle_consumption_l_per_100km' => (string) $validated['vehicle_consumption_l_per_100km'],
                    'round_trip' => $roundTrip,
                    'customer_rate_per_km' => (string) $validated['customer_rate_per_km'],
                    'total_distance_km' => $calculation['total_distance_km'],
                    'fuel_litres' => $calculation['fuel_litres'],
                    'fuel_cost' => $calculation['fuel_cost'],
                    'customer_charge' => $calculation['customer_charge'],
                ],
            ]);
        });

        return redirect()
            ->route('work.travel.index', $event)
            ->with('success', 'Travel calculation saved.');
    }

    private function ensureBusiness(Event $event, Request $request): void
    {
        abort_unless((int) $event->business_id === app(CurrentBusiness::class)->id($request->user()), 404);
    }
}

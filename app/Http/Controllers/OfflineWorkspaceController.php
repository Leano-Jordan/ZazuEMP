<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use App\Support\CurrentBusiness;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfflineWorkspaceController extends Controller
{
    public function shell(): View
    {
        return view('offline');
    }

    public function bootstrap(Request $request, CurrentBusiness $currentBusiness): JsonResponse
    {
        $business = $currentBusiness->resolve($request->user());

        abort_unless($business, 403);

        return response()->json([
            'version' => now()->toIso8601String(),
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'currency' => $business->currency,
            ],
            'customers' => Customer::query()
                ->where('business_id', $business->id)
                ->with('primaryContact')
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (Customer $customer) => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'legal_name' => $customer->legal_name,
                    'phone' => $customer->primaryContact?->phone,
                    'email' => $customer->primaryContact?->email,
                    'notes' => $customer->notes,
                ])
                ->values(),
            'events' => Event::query()
                ->where('business_id', $business->id)
                ->with('customer')
                ->latest('event_date')
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (Event $event) => [
                    'id' => $event->id,
                    'reference' => $event->reference,
                    'name' => $event->name,
                    'event_type' => $event->event_type,
                    'customer_id' => $event->customer_id,
                    'customer_name' => $event->customer?->name ?? $event->customer_name,
                    'event_date' => $event->event_date?->toDateString(),
                    'event_address' => $event->event_address,
                    'status' => $event->status,
                    'notes' => $event->notes,
                ])
                ->values(),
            'quotes' => Quote::query()
                ->whereHas('event', fn ($query) => $query->where('business_id', $business->id))
                ->with('event')
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (Quote $quote) => [
                    'id' => $quote->id,
                    'reference' => $quote->reference,
                    'status' => $quote->status,
                    'currency' => $quote->currency,
                    'event_id' => $quote->event_id,
                    'event_name' => $quote->event?->name,
                ])
                ->values(),
            'capabilities' => BusinessCapability::query()
                ->where('business_id', $business->id)
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (BusinessCapability $capability) => [
                    'id' => $capability->id,
                    'name' => $capability->name,
                    'description' => $capability->description,
                    'active' => (bool) $capability->is_active,
                ])
                ->values(),
        ]);
    }
}

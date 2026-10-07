<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Supplier;
use App\Support\CurrentBusiness;
use App\Support\Offline\SyncEntityIdentityRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfflineWorkspaceController extends Controller
{
    public function shell(): View
    {
        return view('offline');
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function bootstrap(Request $request, CurrentBusiness $currentBusiness, SyncEntityIdentityRegistry $registry): JsonResponse
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
                    'local_id' => $registry->identify($customer, 'customer')->entity_uuid,
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
                    'local_id' => $registry->identify($event, 'job')->entity_uuid,
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
                ->with(['event', 'latestVersion.items'])
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (Quote $quote) => [
                    'id' => $quote->id,
                    'local_id' => $registry->identify($quote, 'quote')->entity_uuid,
                    'reference' => $quote->reference,
                    'status' => $quote->status,
                    'currency' => $quote->currency,
                    'event_id' => $quote->event_id,
                    'event_name' => $quote->event?->name,
                    'latest_version' => $quote->latestVersion?->toArray(),
                ])
                ->values(),
            'suppliers' => Supplier::query()
                ->where('business_id', $business->id)
                ->orderBy('name')
                ->limit(500)
                ->get()
                ->map(fn (Supplier $supplier) => [
                    'id' => $supplier->id,
                    'local_id' => $registry->identify($supplier, 'supplier')->entity_uuid,
                    'name' => $supplier->name,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                ])
                ->values(),
            'purchase_orders' => PurchaseOrder::query()
                ->where('business_id', $business->id)
                ->with('items')
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (PurchaseOrder $order) => [
                    'id' => $order->id,
                    'local_id' => $registry->identify($order, 'purchase_order')->entity_uuid,
                    'reference' => $order->reference,
                    'supplier_id' => $order->supplier_id,
                    'supplier_local_id' => $registry->identify($order->supplier, 'supplier')->entity_uuid,
                    'event_id' => $order->event_id,
                    'currency' => $order->currency,
                    'status' => $order->status,
                    'expected_at' => $order->expected_at,
                    'notes' => $order->notes,
                    'total_amount' => $order->total_amount,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'local_id' => $registry->identify($item, 'purchase_order_item')->entity_uuid,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'received_quantity' => $item->received_quantity,
                        'unit' => $item->unit,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->line_total,
                    ])->values(),
                ])
                ->values(),
            'capabilities' => BusinessCapability::query()
                ->where('business_id', $business->id)
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (BusinessCapability $capability) => [
                    'id' => $capability->id,
                    'local_id' => $registry->identify($capability, 'service')->entity_uuid,
                    'name' => $capability->name,
                    'description' => $capability->description,
                    'active' => (bool) $capability->is_active,
                ])
                ->values(),
        ]);
    }
}

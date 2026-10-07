<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Asset;
use App\Models\EventCost;
use App\Models\FinanceExpense;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
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
            'invoices' => Invoice::query()
                ->where('business_id', $business->id)
                ->with(['items', 'payments'])
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (Invoice $invoice) => [
                    'id' => $invoice->id,
                    'local_id' => $registry->identify($invoice, 'invoice')->entity_uuid,
                    'number' => $invoice->number,
                    'event_id' => $invoice->event_id,
                    'quote_id' => $invoice->quote_id,
                    'quote_version_id' => $invoice->quote_version_id,
                    'status' => $invoice->status,
                    'currency' => $invoice->currency,
                    'subtotal' => $invoice->subtotal,
                    'tax_total' => $invoice->tax_total,
                    'total' => $invoice->total,
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'issued_at' => $invoice->issued_at?->toDateString(),
                    'due_at' => $invoice->due_at?->toDateString(),
                    'notes' => $invoice->notes,
                    'items' => $invoice->items->map(fn ($item) => [
                        'id' => $item->id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->line_total,
                    ])->values(),
                    'payments' => $invoice->payments->map(fn (Payment $payment) => [
                        'id' => $payment->id,
                        'local_id' => $registry->identify($payment, 'payment')->entity_uuid,
                        'type' => $payment->type,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'method' => $payment->method,
                        'reference' => $payment->reference,
                        'paid_at' => $payment->paid_at?->toDateString(),
                    ])->values(),
                ])
                ->values(),
            'inventory_items' => InventoryItem::query()
                ->where('business_id', $business->id)
                ->with('movements')
                ->orderBy('name')
                ->limit(500)
                ->get()
                ->map(fn (InventoryItem $item) => [
                    'id' => $item->id,
                    'local_id' => $registry->identify($item, 'inventory_item')->entity_uuid,
                    'capability_id' => $item->capability_id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'unit' => $item->unit,
                    'reorder_level' => $item->reorder_level,
                    'on_hand' => $item->on_hand,
                    'movements' => $item->movements->map(fn ($movement) => [
                        'id' => $movement->id,
                        'local_id' => $registry->identify($movement, 'inventory_movement')->entity_uuid,
                        'inventory_item_id' => $movement->inventory_item_id,
                        'event_id' => $movement->event_id,
                        'purchase_order_id' => $movement->purchase_order_id,
                        'purchase_order_item_id' => $movement->purchase_order_item_id,
                        'type' => $movement->type,
                        'quantity' => $movement->quantity,
                        'unit_cost' => $movement->unit_cost,
                        'movement_date' => $movement->movement_date?->toDateString(),
                        'reference' => $movement->reference,
                        'notes' => $movement->notes,
                    ])->values(),
                ])
                ->values(),
            'assets' => Asset::query()
                ->where('business_id', $business->id)
                ->with(['allocations' => fn ($query) => $query->where('status', 'allocated')])
                ->orderBy('name')
                ->limit(500)
                ->get()
                ->map(fn (Asset $asset) => [
                    'id' => $asset->id,
                    'local_id' => $registry->identify($asset, 'asset')->entity_uuid,
                    'capability_id' => $asset->capability_id,
                    'asset_tag' => $asset->asset_tag,
                    'name' => $asset->name,
                    'status' => $asset->status,
                    'condition' => $asset->condition,
                    'location' => $asset->location,
                    'acquired_at' => $asset->acquired_at?->toDateString(),
                    'purchase_cost' => $asset->purchase_cost,
                    'currency' => $asset->currency,
                    'notes' => $asset->notes,
                    'allocations' => $asset->allocations->map(fn ($allocation) => [
                        'id' => $allocation->id,
                        'local_id' => $registry->identify($allocation, 'asset_allocation')->entity_uuid,
                        'asset_id' => $allocation->asset_id,
                        'event_id' => $allocation->event_id,
                        'allocated_from' => $allocation->allocated_from?->toDateString(),
                        'allocated_until' => $allocation->allocated_until?->toDateString(),
                        'status' => $allocation->status,
                        'notes' => $allocation->notes,
                    ])->values(),
                ])
                ->values(),
            'costs' => EventCost::query()
                ->where('business_id', $business->id)
                ->with('event')
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (EventCost $cost) => [
                    'id' => $cost->id,
                    'local_id' => $registry->identify($cost, 'event_cost')->entity_uuid,
                    'event_id' => $cost->event_id,
                    'event_name' => $cost->event?->name,
                    'category' => $cost->category,
                    'description' => $cost->description,
                    'currency' => $cost->currency,
                    'projected_amount' => $cost->projected_amount,
                    'actual_amount' => $cost->actual_amount,
                    'status' => $cost->status,
                    'notes' => $cost->notes,
                ])
                ->values(),
            'expenses' => FinanceExpense::query()
                ->where('business_id', $business->id)
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(fn (FinanceExpense $expense) => [
                    'id' => $expense->id,
                    'local_id' => $registry->identify($expense, 'expense')->entity_uuid,
                    'event_id' => $expense->event_id,
                    'supplier_id' => $expense->supplier_id,
                    'purchase_order_id' => $expense->purchase_order_id,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'currency' => $expense->currency,
                    'expense_date' => $expense->expense_date?->toDateString(),
                    'status' => $expense->status,
                    'reference' => $expense->reference,
                    'notes' => $expense->notes,
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

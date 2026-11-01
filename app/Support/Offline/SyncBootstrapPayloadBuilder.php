<?php

namespace App\Support\Offline;

use App\Models\Asset;
use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\FinanceExpense;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\SyncDevice;
use Illuminate\Database\Eloquent\Model;

class SyncBootstrapPayloadBuilder
{
    public const SELECTION_TYPES = [
        'customer' => Customer::class,
        'job' => Event::class,
        'quote' => Quote::class,
        'service' => BusinessCapability::class,
        'preparation' => EventPreparationItem::class,
        'supplier' => Supplier::class,
        'purchase_order' => PurchaseOrder::class,
    ];

    public function build(SyncDevice $device): array
    {
        $business = Business::query()->findOrFail($device->business_id);
        $registry = app(SyncEntityIdentityRegistry::class);

        return [
            'version' => 1,
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'currency' => $business->currency,
                'email' => $business->email,
                'phone' => $business->phone,
                'address' => $business->address,
                'website' => $business->website,
                'tax_number' => $business->tax_number,
            ],
            'catalogue' => $this->bootstrapCatalogue($business, $registry),
            'suppliers' => $this->bootstrapSuppliers($business, $registry),
            'purchase_orders' => $this->bootstrapPurchaseOrders($business, $registry),
            'invoices' => $this->bootstrapInvoices($business, $registry),
            'expenses' => $this->bootstrapExpenses($business, $registry),
            'inventory_items' => $this->bootstrapInventoryItems($business, $registry),
            'assets' => $this->bootstrapAssets($business, $registry),
            'costs' => $this->bootstrapCosts($business, $registry),
            'selected' => $this->bootstrapSelectedRecords($device, $business, $registry),
            'device' => [
                'id' => $device->id,
                'installation_id' => $device->installation_id,
            ],
        ];
    }

    /**
     * @return array<int, array{type: string, id: int}>
     */
    public function defaultSelection(Business $business): array
    {
        $queries = [
            'customer' => Customer::query()
                ->where('business_id', $business->id)
                ->latest('updated_at')
                ->latest('id'),
            'job' => Event::query()
                ->where('business_id', $business->id)
                ->latest('updated_at')
                ->latest('id'),
            'quote' => Quote::query()
                ->whereHas('event', fn ($event) => $event->where('business_id', $business->id))
                ->latest('updated_at')
                ->latest('id'),
            'preparation' => EventPreparationItem::query()
                ->where('business_id', $business->id)
                ->latest('updated_at')
                ->latest('id'),
        ];

        $selection = [];
        foreach ($queries as $type => $query) {
            foreach ($query->limit(25)->pluck('id') as $id) {
                $selection[] = ['type' => $type, 'id' => (int) $id];
            }
        }

        return $selection;
    }

    private function bootstrapCatalogue(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return BusinessCapability::query()
            ->where('business_id', $business->id)
            ->get()
            ->map(fn (BusinessCapability $capability) => [
                'local_id' => $registry->identify($capability, 'service')->entity_uuid,
                'server_id' => $capability->id,
                'record' => $capability->toArray(),
            ])
            ->values();
    }

    private function bootstrapSuppliers(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return Supplier::query()
            ->where('business_id', $business->id)
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn (Supplier $supplier) => [
                'local_id' => $registry->identify($supplier, 'supplier')->entity_uuid,
                'server_id' => $supplier->id,
                'record' => $supplier->toArray(),
            ])
            ->values();
    }

    private function bootstrapPurchaseOrders(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->with('items')
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn (PurchaseOrder $order) => [
                'local_id' => $registry->identify($order, 'purchase_order')->entity_uuid,
                'server_id' => $order->id,
                'record' => $order->toArray(),
                'items' => $order->items->map(fn ($item) => [
                    'local_id' => $registry->identify($item, 'purchase_order_item')->entity_uuid,
                    'server_id' => $item->id,
                    'record' => $item->toArray(),
                ])->values(),
            ])
            ->values();
    }

    private function bootstrapInvoices(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return Invoice::query()
            ->where('business_id', $business->id)
            ->with(['items', 'payments'])
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'local_id' => $registry->identify($invoice, 'invoice')->entity_uuid,
                'server_id' => $invoice->id,
                'record' => array_merge($invoice->toArray(), [
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'items' => $invoice->items->map(fn ($item) => $item->toArray())->values(),
                    'payments' => $invoice->payments->map(fn (Payment $payment) => [
                        'local_id' => $registry->identify($payment, 'payment')->entity_uuid,
                        'server_id' => $payment->id,
                        'record' => $payment->toArray(),
                    ])->values(),
                ]),
            ])
            ->values();
    }

    private function bootstrapInventoryItems(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return InventoryItem::query()
            ->where('business_id', $business->id)
            ->with('movements')
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn (InventoryItem $item) => [
                'local_id' => $registry->identify($item, 'inventory_item')->entity_uuid,
                'server_id' => $item->id,
                'record' => array_merge($item->toArray(), [
                    'on_hand' => $item->on_hand,
                    'movements' => $item->movements->map(fn ($movement) => [
                        'local_id' => $registry->identify($movement, 'inventory_movement')->entity_uuid,
                        'server_id' => $movement->id,
                        'record' => $movement->toArray(),
                    ])->values()->all(),
                ]),
            ])
            ->values();
    }

    private function bootstrapAssets(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return Asset::query()
            ->where('business_id', $business->id)
            ->with(['allocations' => fn ($query) => $query->where('status', 'allocated')])
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn (Asset $asset) => [
                'local_id' => $registry->identify($asset, 'asset')->entity_uuid,
                'server_id' => $asset->id,
                'record' => array_merge($asset->toArray(), [
                    'allocations' => $asset->allocations->map(fn ($allocation) => [
                        'local_id' => $registry->identify($allocation, 'asset_allocation')->entity_uuid,
                        'server_id' => $allocation->id,
                        'record' => $allocation->toArray(),
                    ])->values()->all(),
                ]),
            ])
            ->values();
    }

    private function bootstrapCosts(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return EventCost::query()
            ->where('business_id', $business->id)
            ->with('event')
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(fn (EventCost $cost) => [
                'local_id' => $registry->identify($cost, 'event_cost')->entity_uuid,
                'server_id' => $cost->id,
                'record' => array_merge($cost->toArray(), [
                    'event_name' => $cost->event?->name,
                ]),
            ])
            ->values();
    }

    private function bootstrapExpenses(Business $business, SyncEntityIdentityRegistry $registry)
    {
        return FinanceExpense::query()
            ->where('business_id', $business->id)
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(fn (FinanceExpense $expense) => [
                'local_id' => $registry->identify($expense, 'expense')->entity_uuid,
                'server_id' => $expense->id,
                'record' => $expense->toArray(),
            ])
            ->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bootstrapSelectedRecords(
        SyncDevice $device,
        Business $business,
        SyncEntityIdentityRegistry $registry,
    ): array {
        $metadata = $device->metadata ?? [];
        $selectionMode = $metadata['bootstrap_selection_mode']
            ?? (! empty($metadata['bootstrap_selection']) ? 'explicit' : 'recent');
        $selection = $selectionMode === 'recent'
            ? $this->defaultSelection($business)
            : ($metadata['bootstrap_selection'] ?? []);

        $selected = [];
        foreach ($selection as $item) {
            $record = $this->findSelectedRecord($business, $item);
            if (! $record) {
                continue;
            }

            $identity = $registry->identify($record, $item['type']);
            $selected[] = [
                'type' => $item['type'],
                'local_id' => $identity->entity_uuid,
                'server_id' => $record->id,
                'record' => $this->selectedRecordData($record, $registry),
            ];
        }

        return $selected;
    }

    private function findSelectedRecord(Business $business, array $item): ?Model
    {
        $type = $item['type'] ?? '';
        $model = self::SELECTION_TYPES[$type] ?? null;
        if (! $model) {
            return null;
        }

        $query = $model::query();
        if ($type === 'quote') {
            $query->whereHas('event', fn ($event) => $event->where('business_id', $business->id));
        } else {
            $query->where('business_id', $business->id);
        }

        return $query->whereKey($item['id'] ?? null)->first();
    }

    private function selectedRecordData(Model $record, SyncEntityIdentityRegistry $registry): array
    {
        if ($record instanceof Customer) {
            return $this->selectedCustomerData($record);
        }
        if ($record instanceof Quote) {
            return $this->selectedQuoteData($record, $registry);
        }
        if ($record instanceof Event) {
            return $this->selectedJobData($record, $registry);
        }
        if ($record instanceof EventPreparationItem) {
            return $this->selectedPreparationData($record, $registry);
        }

        return $record->toArray();
    }

    private function selectedCustomerData(Customer $customer): array
    {
        $customer->loadMissing('contacts');
        $data = $customer->toArray();
        $primaryContact = $customer->contacts->firstWhere('is_primary', true);
        $data['contact_name'] = $primaryContact?->name;
        $data['phone'] = $primaryContact?->phone;
        $data['email'] = $primaryContact?->email;

        return $data;
    }

    private function selectedQuoteData(Quote $quote, SyncEntityIdentityRegistry $registry): array
    {
        $quote->loadMissing('latestVersion.items', 'event.customer');
        $data = $quote->toArray();
        $data['event_local_id'] = $quote->event
            ? $registry->identify($quote->event, 'job')->entity_uuid
            : null;
        $data['event_name'] = $quote->event?->name;

        return $data;
    }

    private function selectedJobData(Event $event, SyncEntityIdentityRegistry $registry): array
    {
        $event->loadMissing('requirements', 'customer');
        $data = $event->toArray();
        $data['customer_local_id'] = $event->customer
            ? $registry->identify($event->customer, 'customer')->entity_uuid
            : null;
        $data['requirements'] = $event->requirements->map(function ($requirement) use ($registry): array {
            return [
                'local_id' => $registry->identify($requirement, 'event_requirement')->entity_uuid,
                'server_id' => $requirement->id,
                'record' => $requirement->toArray(),
            ];
        })->values()->all();

        return $data;
    }

    private function selectedPreparationData(
        EventPreparationItem $preparation,
        SyncEntityIdentityRegistry $registry,
    ): array {
        $preparation->loadMissing('event.customer');
        $data = $preparation->toArray();
        $data['event_local_id'] = $preparation->event
            ? $registry->identify($preparation->event, 'job')->entity_uuid
            : null;
        $data['event_name'] = $preparation->event?->name;

        return $data;
    }

}

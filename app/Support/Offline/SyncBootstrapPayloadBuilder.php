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
use App\Models\SyncDelivery;
use App\Models\SyncEntityIdentity;
use Illuminate\Support\Facades\DB;
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

    public function build(SyncDevice $device, array $deliverySequences = []): array
    {
        return DB::transaction(function () use ($device): array {
            // The snapshot and its revisions must describe one serialized
            // business state. Every online aggregate writer takes this same
            // business lock before changing records and advancing revisions.
            $business = Business::query()
                ->whereKey($device->business_id)
                ->lockForUpdate()
                ->firstOrFail();
            $registry = app(SyncEntityIdentityRegistry::class);
            $deliveryTargets = $this->deliveryTargets($device, $deliverySequences);

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
                'suppliers' => $this->bootstrapSuppliers($business, $registry, $deliveryTargets['supplier'] ?? []),
                'purchase_orders' => $this->bootstrapPurchaseOrders($business, $registry, $deliveryTargets['purchase_order'] ?? []),
                'invoices' => $this->bootstrapInvoices($business, $registry, $deliveryTargets['invoice'] ?? []),
                'expenses' => $this->bootstrapExpenses($business, $registry, $deliveryTargets['expense'] ?? []),
                'inventory_items' => $this->bootstrapInventoryItems($business, $registry, $deliveryTargets['inventory_item'] ?? []),
                'assets' => $this->bootstrapAssets($business, $registry, $deliveryTargets['asset'] ?? []),
                'costs' => $this->bootstrapCosts($business, $registry, $deliveryTargets['event_cost'] ?? []),
                'selected' => $this->bootstrapSelectedRecords($device, $business, $registry, $deliveryTargets['selected'] ?? []),
                'device' => [
                    'id' => $device->id,
                    'installation_id' => $device->installation_id,
                ],
            ];
        });
    }

    /**
     * Include aggregates touched by the exact delivery batch being reconciled.
     * The ordinary offline working set remains bounded, but a delivered update
     * must never be acknowledged when its affected record falls outside that
     * bounded window.
     *
     * @param array<int, mixed> $deliverySequences
     * @return array<string, array<int, mixed>>
     */
    private function deliveryTargets(SyncDevice $device, array $deliverySequences): array
    {
        $sequences = array_values(array_unique(array_filter(
            array_map(static fn ($sequence): int => (int) $sequence, $deliverySequences),
            static fn (int $sequence): bool => $sequence > 0,
        )));

        if ($sequences === []) {
            return [];
        }

        $deliveries = SyncDelivery::query()
            ->where('business_id', $device->business_id)
            ->where('destination_device_id', $device->id)
            ->where('stream', 'business')
            ->whereIn('delivery_sequence', $sequences)
            ->with('mutation')
            ->get();

        $targets = [];
        $resolver = app(SyncMutationRelatedIdentityResolver::class);
        foreach ($deliveries as $delivery) {
            $mutation = $delivery->mutation;
            if (! $mutation) {
                continue;
            }

            $identities = $resolver->resolve($mutation);
            if ($identities === []) {
                $localId = $mutation->payload['local_id'] ?? $mutation->entity_id;
                if (is_string($localId)) {
                    $identity = SyncEntityIdentity::query()
                        ->where('business_id', $device->business_id)
                        ->where('entity_type', $mutation->entity_type)
                        ->where('entity_uuid', $localId)
                        ->first();

                    if ($identity) {
                        $identities[] = [
                            'entity_type' => $identity->entity_type,
                            'server_id' => (int) $identity->record_id,
                        ];
                    }
                }
            }

            foreach ($identities as $identity) {
                $type = (string) ($identity['entity_type'] ?? '');
                $recordId = (int) ($identity['server_id'] ?? 0);
                if ($recordId < 1) {
                    continue;
                }

                $collection = match ($type) {
                    'supplier' => 'supplier',
                    'purchase_order' => 'purchase_order',
                    'invoice' => 'invoice',
                    'expense' => 'expense',
                    'inventory_item' => 'inventory_item',
                    'asset' => 'asset',
                    'event_cost' => 'event_cost',
                    default => null,
                };

                if ($collection !== null) {
                    $targets[$collection][] = $recordId;
                }

                if (in_array($type, ['customer', 'job', 'quote', 'preparation'], true)) {
                    $targets['selected'][] = ['type' => $type, 'id' => $recordId];
                }
            }
        }

        foreach ($targets as $key => $ids) {
            if ($key === 'selected') {
                $unique = [];
                foreach ($ids as $item) {
                    $unique[$item['type'].':'.$item['id']] = $item;
                }
                $targets[$key] = array_values($unique);
            } else {
                $targets[$key] = array_values(array_unique(array_map('intval', $ids)));
            }
        }

        return $targets;
    }

    /**
     * Apply the normal bounded window plus any aggregates required by a
     * receiver's unacknowledged delivery batch.
     */
    private function applyBoundedWorkingSet(
        $query,
        string $modelClass,
        Business $business,
        array $includedIds,
        string $orderColumn,
        string $direction,
    ): void {
        if ($includedIds !== []) {
            $window = $modelClass::query()
                ->select('id')
                ->where('business_id', $business->id)
                ->orderBy($orderColumn, $direction)
                ->limit(500);

            $query->where(function ($builder) use ($includedIds, $window): void {
                $builder->whereIn('id', $includedIds)->orWhereIn('id', $window);
            });
        } else {
            $query->limit(500);
        }

        $query->orderBy($orderColumn, $direction);
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
                ...$this->identityPayload($capability, 'service', $registry),
                'server_id' => $capability->id,
                'record' => $capability->toArray(),
            ])
            ->values();
    }

    private function bootstrapSuppliers(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = Supplier::query()->where('business_id', $business->id);
        $this->applyBoundedWorkingSet($query, Supplier::class, $business, $includedIds, 'name', 'asc');

        return $query->get()
            ->map(fn (Supplier $supplier) => [
                ...$this->identityPayload($supplier, 'supplier', $registry),
                'server_id' => $supplier->id,
                'record' => $supplier->toArray(),
            ])
            ->values();
    }

    private function bootstrapPurchaseOrders(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = PurchaseOrder::query()->where('business_id', $business->id)->with('items');
        $this->applyBoundedWorkingSet($query, PurchaseOrder::class, $business, $includedIds, 'created_at', 'desc');

        return $query->get()
            ->map(fn (PurchaseOrder $order) => [
                ...$this->identityPayload($order, 'purchase_order', $registry),
                'server_id' => $order->id,
                'record' => $order->toArray(),
                'items' => $order->items->map(fn ($item) => [
                    ...$this->identityPayload($item, 'purchase_order_item', $registry),
                    'server_id' => $item->id,
                    'record' => $item->toArray(),
                ])->values(),
            ])
            ->values();
    }

    private function bootstrapInvoices(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = Invoice::query()->where('business_id', $business->id)->with(['items', 'payments', 'quoteVersion']);
        $this->applyBoundedWorkingSet($query, Invoice::class, $business, $includedIds, 'id', 'desc');

        return $query->get()
            ->map(fn (Invoice $invoice) => [
                ...$this->identityPayload($invoice, 'invoice', $registry),
                'server_id' => $invoice->id,
                'record' => array_merge($invoice->toArray(), [
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'deposit_required' => (string) ($invoice->quoteVersion?->deposit_amount ?? '0.00'),
                    'deposit_paid_amount' => $invoice->deposit_paid_amount,
                    'deposit_balance' => $invoice->deposit_balance,
                    'items' => $invoice->items->map(fn ($item) => $item->toArray())->values(),
                    'payments' => $invoice->payments->map(fn (Payment $payment) => [
                        ...$this->identityPayload($payment, 'payment', $registry),
                        'server_id' => $payment->id,
                        'record' => $payment->toArray(),
                    ])->values(),
                ]),
            ])
            ->values();
    }

    private function bootstrapInventoryItems(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = InventoryItem::query()->where('business_id', $business->id)->with('movements');
        $this->applyBoundedWorkingSet($query, InventoryItem::class, $business, $includedIds, 'name', 'asc');

        return $query->get()
            ->map(fn (InventoryItem $item) => [
                ...$this->identityPayload($item, 'inventory_item', $registry),
                'server_id' => $item->id,
                'record' => array_merge($item->toArray(), [
                    'on_hand' => $item->on_hand,
                    'movements' => $item->movements->map(fn ($movement) => [
                        ...$this->identityPayload($movement, 'inventory_movement', $registry),
                        'server_id' => $movement->id,
                        'record' => $movement->toArray(),
                    ])->values()->all(),
                ]),
            ])
            ->values();
    }

    private function bootstrapAssets(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = Asset::query()
            ->where('business_id', $business->id)
            ->with(['allocations' => fn ($query) => $query->where('status', 'allocated')]);
        $this->applyBoundedWorkingSet($query, Asset::class, $business, $includedIds, 'name', 'asc');

        return $query->get()
            ->map(fn (Asset $asset) => [
                ...$this->identityPayload($asset, 'asset', $registry),
                'server_id' => $asset->id,
                'record' => array_merge($asset->toArray(), [
                    'allocations' => $asset->allocations->map(fn ($allocation) => [
                        ...$this->identityPayload($allocation, 'asset_allocation', $registry),
                        'server_id' => $allocation->id,
                        'record' => $allocation->toArray(),
                    ])->values()->all(),
                ]),
            ])
            ->values();
    }

    private function bootstrapCosts(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = EventCost::query()->where('business_id', $business->id)->with('event');
        $this->applyBoundedWorkingSet($query, EventCost::class, $business, $includedIds, 'id', 'desc');

        return $query->get()
            ->map(fn (EventCost $cost) => [
                ...$this->identityPayload($cost, 'event_cost', $registry),
                'server_id' => $cost->id,
                'record' => array_merge($cost->toArray(), [
                    'event_name' => $cost->event?->name,
                ]),
            ])
            ->values();
    }

    private function bootstrapExpenses(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = FinanceExpense::query()->where('business_id', $business->id);
        $this->applyBoundedWorkingSet($query, FinanceExpense::class, $business, $includedIds, 'id', 'desc');

        return $query->get()
            ->map(fn (FinanceExpense $expense) => [
                ...$this->identityPayload($expense, 'expense', $registry),
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
        array $additionalSelection = [],
    ): array {
        $metadata = $device->metadata ?? [];
        $selectionMode = $metadata['bootstrap_selection_mode']
            ?? (! empty($metadata['bootstrap_selection']) ? 'explicit' : 'recent');
        $selection = $selectionMode === 'recent'
            ? $this->defaultSelection($business)
            : ($metadata['bootstrap_selection'] ?? []);

        $selection = array_merge($selection, $additionalSelection);
        $selected = [];
        $seen = [];
        foreach ($selection as $item) {
            $selectionKey = (string) ($item['type'] ?? '').':'.(int) ($item['id'] ?? 0);
            if (isset($seen[$selectionKey])) {
                continue;
            }
            $seen[$selectionKey] = true;
            $record = $this->findSelectedRecord($business, $item);
            if (! $record) {
                continue;
            }

            $identity = $registry->identify($record, $item['type']);
            $selected[] = [
                'type' => $item['type'],
                'local_id' => $identity->entity_uuid,
                'revision' => (int) $identity->revision,
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


    /**
     * @return array{local_id:string,revision:int}
     */
    private function identityPayload(Model $record, string $entityType, SyncEntityIdentityRegistry $registry): array
    {
        $identity = $registry->identify($record, $entityType);

        return [
            'local_id' => $identity->entity_uuid,
            'revision' => (int) $identity->revision,
        ];
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

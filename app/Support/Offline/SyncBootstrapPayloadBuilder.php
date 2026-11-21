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
use App\Models\SyncEntityIdentity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($device, $deliverySequences): array {
            // The snapshot and its revisions must describe one serialized
            // business state. Every online aggregate writer takes this same
            // business lock before changing records and advancing revisions.
            $business = Business::query()
                ->whereKey($device->business_id)
                ->lockForUpdate()
                ->firstOrFail();
            $registry = app(SyncEntityIdentityRegistry::class);
            $deliveryTargets = app(SyncBootstrapDeliveryTargetResolver::class)->resolve($device, $deliverySequences);

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
     * Apply the normal bounded window plus any aggregates required by a
     * receiver's unacknowledged delivery batch.
     *
     * @param array<int, int> $includedIds
     */
    private function applyBoundedWorkingSet(
        Builder $query,
        string $modelClass,
        Business $business,
        array $includedIds,
        string $entityType,
        string $orderColumn,
        string $direction,
    ): void {
        $record = new $modelClass();
        $table = $record->getTable();
        $revisionTimestamp = SyncEntityIdentity::query()
            ->select('updated_at')
            ->where('business_id', $business->id)
            ->where('entity_type', $entityType)
            ->where('record_type', $record->getMorphClass())
            ->whereColumn('record_id', $table.'.id')
            ->limit(1);

        // Choose the most recently changed 500 aggregates for synchronization.
        // The final collection remains in its normal display order. Records
        // without an identity yet fall back to their Eloquent updated_at.
        $window = $modelClass::query()
            ->select($table.'.id')
            ->where($table.'.business_id', $business->id)
            ->orderByRaw(
                'COALESCE(('.$revisionTimestamp->toSql().'), '.$table.'.updated_at) DESC',
                $revisionTimestamp->getBindings(),
            )
            ->orderByDesc($table.'.id')
            ->limit(500);

        if ($includedIds !== []) {
            $query->where(function (Builder $builder) use ($includedIds, $window): void {
                $builder->whereIn('id', $includedIds)->orWhereIn('id', $window);
            });
        } else {
            $query->whereIn('id', $window);
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
        $this->applyBoundedWorkingSet($query, Supplier::class, $business, $includedIds, 'supplier', 'name', 'asc');

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
        $this->applyBoundedWorkingSet($query, PurchaseOrder::class, $business, $includedIds, 'purchase_order', 'created_at', 'desc');

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
        $query = Invoice::query()->where('business_id', $business->id)->with(['items', 'payments', 'quoteVersion.quote.event']);
        $this->applyBoundedWorkingSet($query, Invoice::class, $business, $includedIds, 'invoice', 'id', 'desc');

        return $query->get()
            ->map(function (Invoice $invoice) use ($business, $registry): array {
                $quoteVersion = $invoice->quoteVersion;
                if (
                    ! $quoteVersion
                    || (int) $quoteVersion->quote_id !== (int) $invoice->quote_id
                    || (int) ($quoteVersion->quote?->event?->business_id ?? 0) !== (int) $business->id
                ) {
                    $quoteVersion = null;
                } else {
                    $quoteVersion->unsetRelation('quote');
                }
                $invoice->setRelation('quoteVersion', $quoteVersion);

                return [
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
            ];
            })
            ->values();
    }

    private function bootstrapInventoryItems(Business $business, SyncEntityIdentityRegistry $registry, array $includedIds = [])
    {
        $query = InventoryItem::query()->where('business_id', $business->id)->with('movements');
        $this->applyBoundedWorkingSet($query, InventoryItem::class, $business, $includedIds, 'inventory_item', 'name', 'asc');

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
        $this->applyBoundedWorkingSet($query, Asset::class, $business, $includedIds, 'asset', 'name', 'asc');

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
        $this->applyBoundedWorkingSet($query, EventCost::class, $business, $includedIds, 'event_cost', 'id', 'desc');

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
        $this->applyBoundedWorkingSet($query, FinanceExpense::class, $business, $includedIds, 'expense', 'id', 'desc');

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

        if ($selectionMode === 'explicit' && $additionalSelection !== []) {
            $pinned = array_fill_keys(array_map(
                static fn (array $item): string => (string) ($item['type'] ?? '').':'.(int) ($item['id'] ?? 0),
                $selection,
            ), true);
            $additionalSelection = array_values(array_filter(
                $additionalSelection,
                static fn (array $item): bool => isset($pinned[(string) ($item['type'] ?? '').':'.(int) ($item['id'] ?? 0)]),
            ));
        }

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
        $quote->loadMissing('latestVersion.items');
        $businessId = (int) $quote->event()->value('business_id');
        $event = $businessId > 0
            ? $quote->event()
                ->where('business_id', $businessId)
                ->with(['customer' => fn ($customer) => $customer->where('business_id', $businessId)])
                ->first()
            : null;
        $quote->setRelation('event', $event);

        $data = $quote->toArray();
        $data['event_local_id'] = $event
            ? $registry->identify($event, 'job')->entity_uuid
            : null;
        $data['event_name'] = $event?->name;

        return $data;
    }

    private function selectedJobData(Event $event, SyncEntityIdentityRegistry $registry): array
    {
        $event->loadMissing('requirements');
        $customer = $event->customer()->where('business_id', $event->business_id)->first();
        $event->setRelation('customer', $customer);

        $data = $event->toArray();
        $data['customer_local_id'] = $customer
            ? $registry->identify($customer, 'customer')->entity_uuid
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
        $event = $preparation->event()
            ->where('business_id', $preparation->business_id)
            ->with(['customer' => fn ($customer) => $customer->where('business_id', $preparation->business_id)])
            ->first();
        $preparation->setRelation('event', $event);

        $data = $preparation->toArray();
        $data['event_local_id'] = $event
            ? $registry->identify($event, 'job')->entity_uuid
            : null;
        $data['event_name'] = $event?->name;

        return $data;
    }

}

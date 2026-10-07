<?php

namespace App\Support\Offline;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Asset;
use App\Models\AssetAllocation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use App\Services\EventLifecycleService;
use App\Services\FinanceTransactionService;
use App\Services\PurchaseOrderReceivingService;
use App\Support\Audit;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OfflineDomainMutationHandler implements SyncMutationHandler
{
    public function __construct(
        private readonly SyncEntityIdentityRegistry $identities,
        private readonly EventLifecycleService $lifecycle,
        private readonly PurchaseOrderReceivingService $receivingService,
        private readonly FinanceTransactionService $finance,
    ) {
    }

    public function apply(SyncMutation $mutation): void
    {
        $record = match ($mutation->entity_type) {
            'customer' => $this->customer($mutation),
            'job' => $this->event($mutation),
            'service' => $this->capability($mutation),
            'supplier' => $this->supplier($mutation),
            'preparation' => $this->preparation($mutation),
            'purchase_order' => $this->purchaseOrder($mutation),
            'quote' => $this->quote($mutation),
            'quote_acceptance' => $this->quoteAcceptance($mutation),
            'invoice' => $this->invoice($mutation),
            'payment' => $this->payment($mutation),
            'expense' => $this->expense($mutation),
            'purchase_receipt' => $this->purchaseReceipt($mutation),
            'inventory_item' => $this->inventoryItem($mutation),
            'inventory_movement' => $this->inventoryMovement($mutation),
            'event_cost' => $this->eventCost($mutation),
            'asset' => $this->asset($mutation),
            'asset_allocation' => $this->assetAllocation($mutation),
            default => throw ValidationException::withMessages([
                'entity_type' => 'This offline entity is not enabled for server application yet.',
            ]),
        };

        if ($record instanceof Model) {
            $this->identities->register($record, $mutation->entity_type, $this->entityUuid($mutation));
        }
    }

    private function customer(SyncMutation $mutation): Customer
    {
        $payload = $this->recordPayload($mutation);
        $customer = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $customer instanceof Customer) {
            throw ValidationException::withMessages(['entity_id' => 'The offline customer does not exist on the server.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($customer instanceof Customer) {
            $customer->update($attributes);
        } else {
            $customer = Customer::create($attributes);
        }

        $phone = $this->nullableString($payload['phone'] ?? null, 64);
        $email = $this->nullableString($payload['email'] ?? null, 255);

        if ($phone !== null || $email !== null) {
            $contact = $customer->contacts()->where('is_primary', true)->first()
                ?? $customer->contacts()->first();

            if ($contact) {
                $contact->update(['phone' => $phone, 'email' => $email]);
            } else {
                CustomerContact::create([
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $phone,
                    'email' => $email,
                    'label' => 'Primary',
                    'is_primary' => true,
                ]);
            }
        }

        return $customer->fresh();
    }

    private function event(SyncMutation $mutation): Event
    {
        $payload = $this->recordPayload($mutation);
        $event = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $event instanceof Event) {
            throw ValidationException::withMessages(['entity_id' => 'The offline job does not exist on the server.']);
        }

        $status = $event?->status ?: 'draft';
        $attributes = [
            'business_id' => $mutation->business_id,
            'reference' => $event?->reference ?: 'OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'customer_name' => $this->nullableString($payload['customer_name'] ?? null, 255),
            'event_date' => $payload['event_date'] ?? null,
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            'status' => $status,
        ];

        if ($event instanceof Event) {
            $event->update($attributes);
            return $event->fresh();
        }

        return Event::create($attributes);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function preparation(SyncMutation $mutation): EventPreparationItem
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'This offline preparation operation is not supported.']);
        }

        $payload = $this->recordPayload($mutation);
        $eventUuid = $payload['event_local_id'] ?? null;

        if (! is_string($eventUuid) || ! Str::isUuid($eventUuid)) {
            throw ValidationException::withMessages(['payload' => 'A valid local job identity is required for offline preparation.']);
        }

        $eventIdentity = SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', 'job')
            ->where('entity_uuid', Str::lower($eventUuid))
            ->first();

        if (! $eventIdentity) {
            throw ValidationException::withMessages(['payload' => 'The offline preparation item references an unknown job.']);
        }

        $event = Event::query()
            ->where('business_id', $mutation->business_id)
            ->find($eventIdentity->record_id);

        if (! $event) {
            throw ValidationException::withMessages(['payload' => 'The offline preparation item references a job that no longer exists.']);
        }

        $lockedEvent = $this->lifecycle->lock($mutation->business_id, $event->id);
        $this->lifecycle->assertOperational($lockedEvent);

        $item = $this->existing($mutation);
        if ($mutation->operation === 'update' && ! $item instanceof EventPreparationItem) {
            throw ValidationException::withMessages(['entity_id' => 'The offline preparation item does not exist on the server.']);
        }

        $status = $this->preparationStatus($payload['status'] ?? 'open');
        $attributes = [
            'business_id' => $lockedEvent->business_id,
            'event_id' => $lockedEvent->id,
            'title' => $this->requiredString($payload['title'] ?? null, 'title', 255),
            'category' => $this->nullableString($payload['category'] ?? null, 100),
            'quantity' => $this->nullableQuantity($payload['quantity'] ?? null),
            'unit' => $this->nullableString($payload['unit'] ?? null, 50),
            'status' => $status,
            'due_date' => $this->nullableDate($payload['due_date'] ?? null),
            'completed_at' => $status === 'ready' ? now() : null,
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($item instanceof EventPreparationItem) {
            $item->update($attributes);
            return $item->fresh();
        }

        return EventPreparationItem::create($attributes);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function quote(SyncMutation $mutation): \App\Models\Quote
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline quotes only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        if (! $event) {
            throw ValidationException::withMessages(['payload' => 'An offline quote requires a local job identity.']);
        }

        $quote = $this->existing($mutation);
        if ($quote instanceof \App\Models\Quote) {
            if ($quote->status !== 'draft') {
                throw ValidationException::withMessages(['quote' => 'Only draft offline quotes can be edited.']);
            }

            $version = $quote->versions()->orderByDesc('version')->firstOrFail();
            $this->writeOfflineQuoteVersion($version, $payload);
            return $quote->fresh(['latestVersion.items']);
        }

        $status = $payload['status'] ?? 'draft';
        if (! in_array($status, ['draft', 'sent'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline quotes may only be created as draft or sent.']);
        }

        $quote = \App\Models\Quote::create([
            'event_id' => $event->id,
            'reference' => $this->nullableString($payload['reference'] ?? null, 100)
                ?? 'QUO-OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'status' => $status,
            'currency' => strtoupper((string) ($payload['currency'] ?? 'ZAR')),
        ]);

        $version = $quote->versions()->create([
            'version' => 1,
            'status' => $status,
            'subtotal' => '0.00',
            'tax_total' => '0.00',
            'total' => '0.00',
            'deposit_percent' => $this->offlineDepositPercent($payload['deposit_percent'] ?? '0.00'),
            'deposit_amount' => '0.00',
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            'tax_rate_id' => null,
            'tax_code' => null,
            'tax_label' => 'No tax',
            'tax_treatment' => 'out_of_scope',
            'tax_rate' => '0.00',
            'tax_snapshot_at' => now(),
        ]);

        $this->writeOfflineQuoteVersion($version, $payload);

        return $quote->fresh(['latestVersion.items']);
    }

    private function writeOfflineQuoteVersion(\App\Models\QuoteVersion $version, array $payload): void
    {
        if (array_key_exists('deposit_percent', $payload)) {
            $version->update([
                'deposit_percent' => $this->offlineDepositPercent($payload['deposit_percent']),
            ]);
        }

        $items = $payload['items'] ?? [];
        if (! is_array($items) || $items === []) {
            throw ValidationException::withMessages(['items' => 'An offline quote requires at least one line.']);
        }

        $version->items()->delete();
        $subtotalCents = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages(['items' => 'Offline quote lines must be objects.']);
            }

            $quantityHundredths = $this->positiveQuantity($item['quantity'] ?? null);
            $unitPriceCents = Money::toCents((string) ($item['unit_price'] ?? '0.00'));
            if ($unitPriceCents < 0) {
                throw ValidationException::withMessages(['items' => 'Offline quote prices cannot be negative.']);
            }

            $lineTotalCents = Money::multiplyQuantityByPrice(
                Money::toHundredths((string) $quantityHundredths),
                $unitPriceCents
            );

            $version->items()->create([
                'description' => $this->requiredString($item['description'] ?? null, 'description', 500),
                'quantity' => number_format($quantityHundredths, 2, '.', ''),
                'unit' => $this->nullableString($item['unit'] ?? null, 100),
                'unit_price' => Money::fromCents($unitPriceCents),
                'line_total' => Money::fromCents($lineTotalCents),
                'capability_id' => null,
                'event_requirement_id' => null,
                'source_snapshot' => [
                    'offline' => true,
                    'quote_currency' => $version->quote->currency,
                ],
            ]);

            $subtotalCents += $lineTotalCents;
        }

        $taxCents = 0;
        $totalCents = $subtotalCents;
        $depositPercent = Money::toCents((string) ($version->deposit_percent ?? '0.00'));
        $depositCents = intdiv(($totalCents * $depositPercent) + 5000, 10000);

        $version->update([
            'subtotal' => Money::fromCents($subtotalCents),
            'tax_total' => Money::fromCents($taxCents),
            'total' => Money::fromCents($totalCents),
            'deposit_amount' => Money::fromCents($depositCents),
        ]);
    }

    private function offlineDepositPercent(mixed $value): string
    {
        if (! is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
            throw ValidationException::withMessages(['deposit_percent' => 'Offline quote deposit percentage must be between 0 and 100.']);
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function quoteAcceptance(SyncMutation $mutation): ?Model
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline quote acceptance only supports create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $quote = $this->resolveIdentityRecord($mutation->business_id, 'quote', $payload['quote_local_id'] ?? null, \App\Models\Quote::class);

        if (! $quote) {
            throw ValidationException::withMessages(['quote_local_id' => 'The offline acceptance references an unknown quote.']);
        }

        $customerName = $this->requiredString($payload['customer_name'] ?? null, 'customer_name', 255);
        $event = $this->lifecycle->lock($mutation->business_id, $quote->event_id);
        $this->lifecycle->assertOperational($event);

        $quote->refresh();
        if ($quote->status !== 'sent') {
            throw ValidationException::withMessages(['quote' => 'Only a sent quote can be accepted offline.']);
        }

        $version = $quote->versions()->orderByDesc('version')->lockForUpdate()->firstOrFail();
        if ($version->status !== 'sent') {
            throw ValidationException::withMessages(['quote' => 'The latest quote version is not awaiting acceptance.']);
        }

        $version->update(['status' => 'accepted']);
        $quote->update(['status' => 'accepted']);
        $this->lifecycle->confirmAfterQuoteAcceptance($event);

        Audit::record('quote.customer.accepted', $quote, [
            'customer_name' => $customerName,
            'quote_version' => $version->version,
            'offline' => true,
        ], $mutation->business_id);

        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function invoice(SyncMutation $mutation): Invoice
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline invoices only support create/upsert.']);
        }

        $existing = $this->existing($mutation);
        if ($existing instanceof Invoice) {
            return $existing->fresh(['items', 'payments']);
        }

        $payload = $this->recordPayload($mutation);
        $quote = $this->resolveIdentityRecord($mutation->business_id, 'quote', $payload['quote_local_id'] ?? null, \App\Models\Quote::class);
        if (! $quote || $quote->status !== 'accepted') {
            throw ValidationException::withMessages(['quote_local_id' => 'Offline invoices require an accepted quote.']);
        }

        $version = $quote->latestVersion()->first();
        if (! $version || $version->status !== 'accepted') {
            throw ValidationException::withMessages(['quote_local_id' => 'The accepted quote version is unavailable offline.']);
        }

        if (Invoice::query()->where('business_id', $mutation->business_id)->where('quote_version_id', $version->id)->exists()) {
            throw ValidationException::withMessages(['quote_local_id' => 'This accepted quote version has already been invoiced.']);
        }

        $business = Business::query()->with('taxProfile')->findOrFail($mutation->business_id);
        $customer = $quote->event?->customer;
        $invoice = Invoice::create([
            'business_id' => $business->id,
            'idempotency_key' => $mutation->mutation_id,
            'event_id' => $quote->event_id,
            'quote_id' => $quote->id,
            'quote_version_id' => $version->id,
            'number' => 'INV-OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'business_legal_name' => $business->taxProfile?->legal_name ?: $business->name,
            'business_trading_name' => $business->taxProfile?->trading_name ?: $business->name,
            'business_address' => $business->address,
            'business_email' => $business->email,
            'business_phone' => $business->phone,
            'business_tax_number' => $business->taxProfile?->income_tax_number ?: $business->tax_number,
            'business_vat_number' => $business->taxProfile?->vat_number,
            'customer_name' => $customer?->legal_name ?: $customer?->name,
            'customer_address' => $customer?->billing_address,
            'customer_email' => $customer?->primaryContact?->email,
            'customer_phone' => $customer?->primaryContact?->phone,
            'customer_tax_number' => $customer?->tax_number,
            'customer_vat_number' => $customer?->vat_number,
            'status' => 'issued',
            'currency' => $quote->currency,
            'subtotal' => $version->subtotal,
            'tax_total' => $version->tax_total,
            'total' => $version->total,
            'issued_at' => $payload['issued_at'] ?? now()->toDateString(),
            'due_at' => $payload['due_at'] ?? now()->addDays(7)->toDateString(),
            'notes' => $version->notes,
            'tax_rate_id' => $version->tax_rate_id,
            'tax_code' => $version->tax_code,
            'tax_label' => $version->tax_label,
            'tax_treatment' => $version->tax_treatment,
            'tax_rate' => $version->tax_rate,
        ]);

        $version->load('items');
        foreach ($version->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
                'quote_item_id' => $item->id,
            ]);
        }

        Audit::record('finance.invoice.created', $invoice, [
            'number' => $invoice->number,
            'total' => $invoice->total,
            'currency' => $invoice->currency,
            'offline' => true,
        ], $business->id);

        return $invoice->fresh(['items', 'payments']);
    }

    private function payment(SyncMutation $mutation): Payment
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline payments only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $invoice = $this->resolveIdentityRecord($mutation->business_id, 'invoice', $payload['invoice_local_id'] ?? null, Invoice::class);
        if (! $invoice) {
            throw ValidationException::withMessages(['invoice_local_id' => 'The offline payment references an unknown invoice.']);
        }

        $data = [
            'invoice_id' => $invoice->id,
            'idempotency_key' => $mutation->mutation_id,
            'type' => $payload['type'] ?? 'payment',
            'amount' => $payload['amount'] ?? null,
            'method' => $payload['method'] ?? null,
            'reference' => $this->nullableString($payload['reference'] ?? null, 255),
            'paid_at' => $payload['paid_at'] ?? now()->toDateString(),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        $this->finance->recordPayment($mutation->business_id, $data, $this->lifecycle);

        return Payment::query()
            ->where('business_id', $mutation->business_id)
            ->where('idempotency_key', $mutation->mutation_id)
            ->firstOrFail();
    }

    private function expense(SyncMutation $mutation): FinanceExpense
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline expenses only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        $order = array_key_exists('purchase_order_local_id', $payload)
            ? $this->resolveIdentityRecord($mutation->business_id, 'purchase_order', $payload['purchase_order_local_id'], PurchaseOrder::class)
            : null;
        $supplier = array_key_exists('supplier_local_id', $payload)
            ? $this->resolveIdentityRecord($mutation->business_id, 'supplier', $payload['supplier_local_id'], Supplier::class)
            : null;

        $this->finance->recordExpense($mutation->business_id, $this->businessCurrency($mutation->business_id), [
            'idempotency_key' => $mutation->mutation_id,
            'event_id' => $event?->id,
            'purchase_order_id' => $order?->id,
            'supplier_id' => $supplier?->id,
            'description' => $this->requiredString($payload['description'] ?? null, 'description', 500),
            'amount' => $payload['amount'] ?? null,
            'status' => $payload['status'] ?? 'unpaid',
            'reference' => $this->nullableString($payload['reference'] ?? null, 255),
            'expense_date' => $payload['expense_date'] ?? now()->toDateString(),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ], $this->lifecycle);

        return FinanceExpense::query()
            ->where('business_id', $mutation->business_id)
            ->where('idempotency_key', $mutation->mutation_id)
            ->firstOrFail();
    }


    private function inventoryItem(SyncMutation $mutation): InventoryItem
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline inventory items only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $item = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $item instanceof InventoryItem) {
            throw ValidationException::withMessages(['entity_id' => 'The offline inventory item does not exist on the server.']);
        }

        $capability = array_key_exists('capability_local_id', $payload)
            ? $this->resolveOfflineCapability($mutation->business_id, $payload['capability_local_id'])
            : null;

        $sku = $this->nullableString($payload['sku'] ?? null, 100);
        if ($sku !== null) {
            $duplicate = InventoryItem::query()
                ->where('business_id', $mutation->business_id)
                ->where('sku', $sku)
                ->when($item instanceof InventoryItem, fn ($query) => $query->where('id', '!=', $item->id))
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages(['sku' => 'An inventory item with this SKU already exists in this business.']);
            }
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'capability_id' => $capability?->id,
            'sku' => $sku,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'unit' => $this->requiredString($payload['unit'] ?? 'unit', 'unit', 50),
            'reorder_level' => number_format((float) ($payload['reorder_level'] ?? '0'), 2, '.', ''),
        ];

        if ($item instanceof InventoryItem) {
            $item->update($attributes);
            return $item->fresh();
        }

        return InventoryItem::create($attributes);
    }

    private function inventoryMovement(SyncMutation $mutation): InventoryMovement
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline inventory movements only support create/upsert.']);
        }

        $existing = $this->existing($mutation);
        if ($existing instanceof InventoryMovement) {
            return $existing->fresh();
        }

        $payload = $this->recordPayload($mutation);
        $item = $this->resolveIdentityRecord(
            $mutation->business_id,
            'inventory_item',
            $payload['inventory_item_local_id'] ?? null,
            InventoryItem::class,
        );

        if (! $item instanceof InventoryItem) {
            throw ValidationException::withMessages(['inventory_item_local_id' => 'The offline inventory movement references an unknown inventory item.']);
        }

        $event = array_key_exists('event_local_id', $payload)
            ? $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'])
            : null;

        $purchaseOrder = array_key_exists('purchase_order_local_id', $payload)
            ? $this->resolveIdentityRecord($mutation->business_id, 'purchase_order', $payload['purchase_order_local_id'], PurchaseOrder::class)
            : null;

        if (array_key_exists('purchase_order_local_id', $payload) && ! $purchaseOrder instanceof PurchaseOrder) {
            throw ValidationException::withMessages(['purchase_order_local_id' => 'The offline inventory movement references an unknown purchase order.']);
        }

        $purchaseOrderItem = null;
        if (array_key_exists('purchase_order_item_local_id', $payload)) {
            $purchaseOrderItem = $this->resolveIdentityRecord(
                $mutation->business_id,
                'purchase_order_item',
                $payload['purchase_order_item_local_id'],
                PurchaseOrderItem::class,
            );

            if (! $purchaseOrderItem instanceof PurchaseOrderItem || ! $purchaseOrder) {
                throw ValidationException::withMessages(['purchase_order_item_local_id' => 'The offline inventory movement references an invalid purchase-order line.']);
            }

            if ((int) $purchaseOrderItem->purchase_order_id !== (int) $purchaseOrder->id) {
                throw ValidationException::withMessages(['purchase_order_item_local_id' => 'The purchase-order line does not belong to the referenced purchase order.']);
            }
        }

        $type = $payload['type'] ?? null;
        if (! is_string($type) || ! in_array($type, ['receipt', 'issue', 'return', 'adjustment_in', 'adjustment_out'], true)) {
            throw ValidationException::withMessages(['type' => 'Offline inventory movement type is invalid.']);
        }

        $quantity = $this->positiveQuantity($payload['quantity'] ?? null);
        $unitCost = $this->nonNegativeMoney($payload['unit_cost'] ?? '0.00');
        $movementDate = $this->nullableDate($payload['movement_date'] ?? now()->toDateString()) ?? now()->toDateString();

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $mutation,
            $item,
            $event,
            $purchaseOrder,
            $purchaseOrderItem,
            $type,
            $quantity,
            $unitCost,
            $movementDate,
            $payload,
            &$existing,
        ): void {
            \App\Models\Business::query()->whereKey($mutation->business_id)->lockForUpdate()->firstOrFail();

            $lockedItem = InventoryItem::query()
                ->where('business_id', $mutation->business_id)
                ->lockForUpdate()
                ->findOrFail($item->id);

            if (in_array($type, ['issue', 'adjustment_out'], true)) {
                $onHand = $lockedItem->on_hand_hundredths;
                $requested = Money::toHundredths((string) $quantity);
                if ($requested > $onHand) {
                    throw ValidationException::withMessages([
                        'quantity' => 'This offline movement would make stock on hand negative.',
                    ]);
                }
            }

            $existing = InventoryMovement::query()
                ->where('business_id', $mutation->business_id)
                ->where('idempotency_key', $mutation->mutation_id)
                ->first();

            if ($existing instanceof InventoryMovement) {
                return;
            }

            $existing = InventoryMovement::create([
                'business_id' => $mutation->business_id,
                'inventory_item_id' => $lockedItem->id,
                'idempotency_key' => $mutation->mutation_id,
                'event_id' => $event?->id,
                'purchase_order_id' => $purchaseOrder?->id,
                'purchase_order_item_id' => $purchaseOrderItem?->id,
                'type' => $type,
                'quantity' => number_format($quantity, 2, '.', ''),
                'unit_cost' => number_format($unitCost, 2, '.', ''),
                'movement_date' => $movementDate,
                'reference' => $this->nullableString($payload['reference'] ?? null, 255),
                'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            ]);

            Audit::record('inventory.movement.recorded', $existing, [
                'type' => $existing->type,
                'quantity' => $existing->quantity,
                'item_id' => $lockedItem->id,
                'offline' => true,
            ], $mutation->business_id);
        });

        return $existing instanceof InventoryMovement ? $existing->fresh() : throw new \LogicException('Offline inventory movement was not recorded.');
    }

    private function eventCost(SyncMutation $mutation): EventCost
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline event costs only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        if (! $event) {
            throw ValidationException::withMessages(['event_local_id' => 'An offline cost requires a valid local job identity.']);
        }

        $cost = $this->existing($mutation);
        if ($mutation->operation !== 'create' && ! $cost instanceof EventCost) {
            throw ValidationException::withMessages(['entity_id' => 'The offline cost does not exist on the server.']);
        }

        $currency = strtoupper((string) ($payload['currency'] ?? ''));
        $businessCurrency = $this->businessCurrency($mutation->business_id);
        if ($currency === '' || $currency !== $businessCurrency) {
            throw ValidationException::withMessages(['currency' => 'Offline costs must use the active business currency.']);
        }

        $status = $payload['status'] ?? 'planned';
        if (! is_string($status) || ! in_array($status, ['planned', 'incurred', 'cancelled'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline cost status is invalid.']);
        }

        $projected = $this->nonNegativeMoney($payload['projected_amount'] ?? '0.00');
        $actual = array_key_exists('actual_amount', $payload) && $payload['actual_amount'] !== null && $payload['actual_amount'] !== ''
            ? $this->nonNegativeMoney($payload['actual_amount'])
            : null;

        if ($status === 'planned' && $actual !== null) {
            throw ValidationException::withMessages(['actual_amount' => 'A planned cost cannot have an actual amount yet.']);
        }
        if ($status === 'cancelled' && $actual !== null) {
            throw ValidationException::withMessages(['actual_amount' => 'A cancelled cost cannot have an actual amount.']);
        }
        if ($status === 'incurred' && $actual === null) {
            throw ValidationException::withMessages(['actual_amount' => 'An incurred cost must have an actual amount.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'event_id' => $event->id,
            'category' => $this->requiredString($payload['category'] ?? null, 'category', 100),
            'description' => $this->requiredString($payload['description'] ?? null, 'description', 255),
            'currency' => $currency,
            'projected_amount' => number_format($projected, 2, '.', ''),
            'actual_amount' => $actual === null ? null : number_format($actual, 2, '.', ''),
            'status' => $status,
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($cost instanceof EventCost) {
            $cost->update($attributes);
            return $cost->fresh();
        }

        return EventCost::create($attributes);
    }

    private function asset(SyncMutation $mutation): Asset
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline assets only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $asset = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $asset instanceof Asset) {
            throw ValidationException::withMessages(['entity_id' => 'The offline asset does not exist on the server.']);
        }

        $capability = array_key_exists('capability_local_id', $payload)
            ? $this->resolveOfflineCapability($mutation->business_id, $payload['capability_local_id'])
            : null;

        $assetTag = $this->requiredString($payload['asset_tag'] ?? null, 'asset_tag', 100);
        $duplicate = Asset::query()
            ->where('business_id', $mutation->business_id)
            ->where('asset_tag', $assetTag)
            ->when($asset instanceof Asset, fn ($query) => $query->whereKey('!=', $asset->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['asset_tag' => 'An asset with this tag already exists in this business.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'capability_id' => $capability?->id,
            'asset_tag' => $assetTag,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'status' => $asset instanceof Asset ? ($asset->status ?: 'available') : 'available',
            'condition' => $this->requiredEnum($payload['condition'] ?? 'good', ['good', 'fair', 'poor', 'damaged'], 'condition'),
            'location' => $this->nullableString($payload['location'] ?? null, 255),
            'acquired_at' => $this->nullableDate($payload['acquired_at'] ?? null),
            'purchase_cost' => number_format($this->nonNegativeMoney($payload['purchase_cost'] ?? '0.00'), 2, '.', ''),
            'currency' => $this->businessCurrency($mutation->business_id),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($asset instanceof Asset) {
            $asset->update($attributes);
            return $asset->fresh();
        }

        return Asset::create($attributes);
    }

    private function assetAllocation(SyncMutation $mutation): AssetAllocation
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline asset allocations only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $allocation = $this->existing($mutation);

        $asset = array_key_exists('asset_local_id', $payload)
            ? $this->resolveIdentityRecord($mutation->business_id, 'asset', $payload['asset_local_id'], Asset::class)
            : ($allocation instanceof AssetAllocation ? $allocation->asset()->where('business_id', $mutation->business_id)->first() : null);

        if (! $asset instanceof Asset) {
            throw ValidationException::withMessages(['asset_local_id' => 'The offline allocation references an unknown asset.']);
        }

        if ($allocation instanceof AssetAllocation) {
            if ($allocation->status === 'returned' && $mutation->operation === 'create') {
                return $allocation->fresh();
            }

            $status = $payload['status'] ?? 'returned';
            if ($status !== 'returned') {
                throw ValidationException::withMessages(['status' => 'Offline asset allocation updates may only return an existing allocation.']);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($mutation, $asset, $allocation, $status, $payload): void {
                $lockedAsset = Asset::query()->where('business_id', $mutation->business_id)->lockForUpdate()->findOrFail($asset->id);
                $lockedAllocation = AssetAllocation::query()
                    ->where('business_id', $mutation->business_id)
                    ->lockForUpdate()
                    ->findOrFail($allocation->id);

                $lockedAllocation->update([
                    'status' => $status,
                    'allocated_until' => $status === 'returned'
                        ? ($lockedAllocation->allocated_until?->toDateString() ?? now()->toDateString())
                        : $this->nullableDate($payload['allocated_until'] ?? null),
                    'notes' => $this->nullableString($payload['notes'] ?? $lockedAllocation->notes, 5000),
                ]);

                $lockedAsset->update(['status' => $status === 'returned' ? 'available' : 'allocated']);

                Audit::record(
                    $status === 'returned' ? 'assets.released' : 'assets.allocated',
                    $lockedAsset,
                    ['allocation_id' => $lockedAllocation->id, 'offline' => true],
                    $mutation->business_id,
                );
            });

            return $allocation->fresh();
        }

        if (! $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null)) {
            throw ValidationException::withMessages(['event_local_id' => 'An offline allocation requires a valid local job identity.']);
        }

        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id']);
        $from = $this->nullableDate($payload['allocated_from'] ?? now()->toDateString());
        $until = $this->nullableDate($payload['allocated_until'] ?? null);
        if ($from === null) {
            throw ValidationException::withMessages(['allocated_from' => 'An allocation start date is required.']);
        }
        if ($until !== null && $until < $from) {
            throw ValidationException::withMessages(['allocated_until' => 'Allocation dates are invalid.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($mutation, $asset, $event, $payload, $from, $until, &$allocation): void {
            $lockedAsset = Asset::query()->where('business_id', $mutation->business_id)->lockForUpdate()->findOrFail($asset->id);
            abort_if($lockedAsset->status !== 'available', 422, 'Only available assets can be allocated offline.');

            $overlap = $lockedAsset->allocations()
                ->where('status', 'allocated')
                ->whereDate('allocated_from', '<=', $until ?: $from)
                ->where(function ($query) use ($from): void {
                    $query->whereNull('allocated_until')->orWhereDate('allocated_until', '>=', $from);
                })
                ->exists();

            abort_if($overlap, 422, 'This asset is already allocated for the selected period.');

            $allocation = $lockedAsset->allocations()->create([
                'business_id' => $mutation->business_id,
                'event_id' => $event->id,
                'allocated_from' => $from,
                'allocated_until' => $until,
                'status' => 'allocated',
                'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            ]);

            Audit::record('assets.allocated', $lockedAsset, [
                'event_id' => $event->id,
                'allocation_id' => $allocation->id,
                'offline' => true,
            ], $mutation->business_id);
        });

        return $allocation->fresh();
    }

    private function resolveOfflineCapability(int $businessId, mixed $uuid): ?BusinessCapability
    {
        if ($uuid === null || $uuid === '') {
            return null;
        }

        return $this->resolveIdentityRecord($businessId, 'service', $uuid, BusinessCapability::class);
    }

    private function requiredEnum(mixed $value, array $allowed, string $field): string
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            throw ValidationException::withMessages([$field => "Offline {$field} is invalid."]);
        }

        return $value;
    }

    private function resolveIdentityRecord(int $businessId, string $entityType, mixed $uuid, string $model): ?Model
    {
        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            throw ValidationException::withMessages(['payload' => "A valid local {$entityType} identity is required."]);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', $entityType)
            ->where('entity_uuid', Str::lower($uuid))
            ->first();

        if (! $identity) {
            return null;
        }

        $query = $model::query()->whereKey($identity->record_id);

        if ($entityType === 'quote') {
            $query->whereHas('event', fn ($event) => $event->where('business_id', $businessId));
        } else {
            $query->where('business_id', $businessId);
        }

        return $query->first();
    }

    private function businessCurrency(int $businessId): string
    {
        return strtoupper((string) Business::query()->whereKey($businessId)->value('currency'));
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function purchaseOrder(SyncMutation $mutation): PurchaseOrder
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline purchase orders only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $existing = $this->existing($mutation);

        if ($existing instanceof PurchaseOrder) {
            return $existing->fresh(['items']);
        }

        $supplier = $this->resolveOfflineSupplier($mutation->business_id, $payload['supplier_local_id'] ?? null);
        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        $currency = strtoupper((string) ($payload['currency'] ?? ''));
        $businessCurrency = strtoupper((string) $supplier->business?->currency);

        if ($currency === '' || $currency !== $businessCurrency) {
            throw ValidationException::withMessages(['currency' => 'Offline purchase orders must use the active business currency.']);
        }

        $lines = $payload['lines'] ?? [];
        if (! is_array($lines) || $lines === []) {
            throw ValidationException::withMessages(['lines' => 'At least one purchase order line is required.']);
        }

        $status = $payload['status'] ?? 'draft';
        if (! in_array($status, ['draft', 'ordered'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline purchase orders may only be created as draft or ordered.']);
        }

        $order = PurchaseOrder::create([
            'business_id' => $mutation->business_id,
            'event_id' => $event?->id,
            'supplier_id' => $supplier->id,
            'idempotency_key' => $mutation->mutation_id,
            'reference' => 'PO-OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'status' => $status,
            'ordered_at' => $status === 'ordered' ? now()->toDateString() : null,
            'currency' => $currency,
            'expected_at' => $this->nullableDate($payload['expected_at'] ?? null),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            'total_amount' => '0.00',
        ]);

        $total = 0.0;
        foreach ($lines as $line) {
            $total += $this->createOfflinePurchaseOrderLine($order, $mutation, $line);
        }

        $order->update(['total_amount' => number_format($total, 2, '.', '')]);

        return $order->fresh(['items']);
    }

    private function resolveOfflineSupplier(int $businessId, mixed $supplierUuid): Supplier
    {
        if (! is_string($supplierUuid) || ! Str::isUuid($supplierUuid)) {
            throw ValidationException::withMessages(['payload' => 'A valid local supplier identity is required.']);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', 'supplier')
            ->where('entity_uuid', Str::lower($supplierUuid))
            ->first();

        $supplier = $identity
            ? Supplier::query()->where('business_id', $businessId)->find($identity->record_id)
            : null;

        if (! $supplier) {
            throw ValidationException::withMessages(['payload' => 'The offline purchase order references an unknown supplier.']);
        }

        return $supplier;
    }

    private function createOfflinePurchaseOrderLine(PurchaseOrder $order, SyncMutation $mutation, mixed $line): float
    {
        if (! is_array($line)) {
            throw ValidationException::withMessages(['lines' => 'Offline purchase order lines must be objects.']);
        }

        $description = $this->requiredString($line['description'] ?? null, 'description', 255);
        $quantity = $this->positiveQuantity($line['quantity'] ?? null);
        $unitPrice = $this->nonNegativeMoney($line['unit_price'] ?? null);
        $lineTotal = round($quantity * $unitPrice, 2);

        $capabilityId = $line['capability_id'] ?? null;
        if ($capabilityId !== null && (
            ! is_numeric($capabilityId)
            || ! BusinessCapability::query()->where('business_id', $mutation->business_id)->whereKey((int) $capabilityId)->exists()
        )) {
            throw ValidationException::withMessages(['lines' => 'An offline purchase order line references an invalid catalogue item.']);
        }

        $lineLocalId = $line['local_id'] ?? null;
        if (! is_string($lineLocalId) || ! Str::isUuid($lineLocalId)) {
            throw ValidationException::withMessages(['lines' => 'Each offline purchase order line requires a valid local identity.']);
        }

        $item = $order->items()->create([
            'business_id' => $mutation->business_id,
            'capability_id' => $capabilityId,
            'description' => $description,
            'quantity' => number_format($quantity, 2, '.', ''),
            'received_quantity' => '0.00',
            'unit' => $this->nullableString($line['unit'] ?? null, 50),
            'unit_price' => number_format($unitPrice, 2, '.', ''),
            'line_total' => number_format($lineTotal, 2, '.', ''),
        ]);
        $this->identities->register($item, 'purchase_order_item', $lineLocalId);

        return $lineTotal;
    }

    private function purchaseReceipt(SyncMutation $mutation): ?Model
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline purchase receipts only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $purchaseOrderUuid = $payload['purchase_order_local_id'] ?? null;
        if (! is_string($purchaseOrderUuid) || ! Str::isUuid($purchaseOrderUuid)) {
            throw ValidationException::withMessages(['payload' => 'A valid local purchase order identity is required.']);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', 'purchase_order')
            ->where('entity_uuid', Str::lower($purchaseOrderUuid))
            ->first();

        $order = $identity
            ? PurchaseOrder::query()->where('business_id', $mutation->business_id)->find($identity->record_id)
            : null;
        if (! $order) {
            throw ValidationException::withMessages(['payload' => 'The offline purchase receipt references an unknown purchase order.']);
        }

        $received = $payload['received_quantities'] ?? [];
        if (! is_array($received) || $received === []) {
            throw ValidationException::withMessages(['received_quantities' => 'At least one receipt quantity is required.']);
        }

        $this->receivingService->receive(
            $order,
            $mutation->business_id,
            $this->resolveOfflineReceiptQuantities($order, $mutation->business_id, $received),
            $mutation->mutation_id,
            $this->lifecycle,
        );

        return null;
    }

    private function resolveOfflineReceiptQuantities(PurchaseOrder $order, int $businessId, array $received): array
    {
        $resolved = [];
        foreach ($received as $lineIdentityOrId => $quantity) {
            $line = $this->resolveOfflinePurchaseOrderLine($order, $businessId, $lineIdentityOrId);
            $resolved[$line->id] = $quantity;
        }

        return $resolved;
    }

    private function resolveOfflinePurchaseOrderLine(PurchaseOrder $order, int $businessId, mixed $lineIdentityOrId): PurchaseOrderItem
    {
        $line = null;

        if (is_string($lineIdentityOrId) && Str::isUuid($lineIdentityOrId)) {
            $lineIdentity = SyncEntityIdentity::query()
                ->where('business_id', $businessId)
                ->where('entity_type', 'purchase_order_item')
                ->where('entity_uuid', Str::lower($lineIdentityOrId))
                ->first();

            if ($lineIdentity) {
                $line = PurchaseOrderItem::query()
                    ->where('business_id', $businessId)
                    ->where('purchase_order_id', $order->id)
                    ->find($lineIdentity->record_id);
            }
        } elseif (is_numeric($lineIdentityOrId)) {
            $line = PurchaseOrderItem::query()
                ->where('business_id', $businessId)
                ->where('purchase_order_id', $order->id)
                ->find((int) $lineIdentityOrId);
        }

        if (! $line) {
            throw ValidationException::withMessages([
                'received_quantities' => 'The receipt references an unknown purchase order line for this business and order.',
            ]);
        }

        return $line;
    }

    private function resolveOfflineEvent(int $businessId, mixed $eventUuid): ?Event
    {
        if ($eventUuid === null || $eventUuid === '') {
            return null;
        }

        if (! is_string($eventUuid) || ! Str::isUuid($eventUuid)) {
            throw ValidationException::withMessages(['payload' => 'The offline purchase order job identity is invalid.']);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', 'job')
            ->where('entity_uuid', Str::lower($eventUuid))
            ->first();

        $event = $identity
            ? Event::query()->where('business_id', $businessId)->find($identity->record_id)
            : null;

        if (! $event) {
            throw ValidationException::withMessages(['payload' => 'The offline purchase order references an unknown job.']);
        }

        $event = $this->lifecycle->lock($businessId, $event->id);
        $this->lifecycle->assertOperational($event);

        return $event;
    }

    private function preparationStatus(mixed $value): string
    {
        if (! is_string($value) || ! in_array($value, ['open', 'blocked', 'ready'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline preparation status must be open, blocked, or ready.']);
        }

        return $value;
    }

    private function nullableQuantity(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value) || (float) $value < 0) {
            throw ValidationException::withMessages(['quantity' => 'Offline preparation quantity must be zero or greater.']);
        }

        return (float) $value;
    }

    private function positiveQuantity(mixed $value): float
    {
        if (! is_numeric($value) || (float) $value <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Offline purchase order quantity must be greater than zero.']);
        }

        return round((float) $value, 2);
    }

    private function nonNegativeMoney(mixed $value): float
    {
        if (! is_numeric($value) || (float) $value < 0) {
            throw ValidationException::withMessages(['unit_price' => 'Offline purchase order price must be zero or greater.']);
        }

        return round((float) $value, 2);
    }

    private function nullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || ! strtotime($value)) {
            throw ValidationException::withMessages(['date' => 'Offline date must be a valid date.']);
        }

        return $value;
    }

    private function supplier(SyncMutation $mutation): Supplier
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline suppliers only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $supplier = $this->existing($mutation);

        $attributes = [
            'business_id' => $mutation->business_id,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'email' => $this->nullableString($payload['email'] ?? null, 255),
            'phone' => $this->nullableString($payload['phone'] ?? null, 64),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($supplier instanceof Supplier) {
            $supplier->update($attributes);
            return $supplier->fresh();
        }

        return Supplier::create($attributes);
    }

    private function capability(SyncMutation $mutation): BusinessCapability
    {
        $payload = $this->recordPayload($mutation);
        $capability = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $capability instanceof BusinessCapability) {
            throw ValidationException::withMessages(['entity_id' => 'The offline service does not exist on the server.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'description' => $this->nullableString($payload['description'] ?? null, 5000),
            'is_active' => (bool) ($payload['active'] ?? true),
            'currency' => 'ZAR',
        ];

        if ($capability instanceof BusinessCapability) {
            $capability->update($attributes);
            return $capability->fresh();
        }

        return BusinessCapability::create($attributes);
    }

    private function existing(SyncMutation $mutation): ?Model
    {
        return SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', $mutation->entity_type)
            ->where('entity_uuid', $this->entityUuid($mutation))
            ->first()?->record;
    }

    private function recordPayload(SyncMutation $mutation): array
    {
        $record = $mutation->payload['record'] ?? null;

        if (! is_array($record)) {
            throw ValidationException::withMessages(['payload' => 'A record payload is required for offline application.']);
        }

        return $record;
    }

    private function entityUuid(SyncMutation $mutation): string
    {
        $uuid = $mutation->payload['local_id'] ?? null;

        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            throw ValidationException::withMessages(['entity_id' => 'A valid local synchronization identity is required.']);
        }

        return Str::lower($uuid);
    }

    private function requiredString(mixed $value, string $field, int $max): string
    {
        if (! is_string($value) || trim($value) === '' || mb_strlen($value) > $max) {
            throw ValidationException::withMessages([$field => "A valid {$field} is required."]);
        }

        return trim($value);
    }

    private function nullableString(mixed $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value) || mb_strlen($value) > $max) {
            throw ValidationException::withMessages(['payload' => 'An offline text field is invalid.']);
        }

        return trim($value);
    }
}

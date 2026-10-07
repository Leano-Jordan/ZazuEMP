<?php

namespace App\Support\Offline;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use App\Services\EventLifecycleService;
use App\Services\PurchaseOrderReceivingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OfflineDomainMutationHandler implements SyncMutationHandler
{
    public function __construct(
        private readonly SyncEntityIdentityRegistry $identities,
        private readonly EventLifecycleService $lifecycle,
        private readonly PurchaseOrderReceivingService $receivingService,
    ) {
    }

    public function apply(SyncMutation $mutation): void
    {
        $record = match ($mutation->entity_type) {
            'customer' => $this->customer($mutation),
            'job' => $this->event($mutation),
            'service' => $this->capability($mutation),
            'preparation' => $this->preparation($mutation),
            'purchase_order' => $this->purchaseOrder($mutation),
            'purchase_receipt' => $this->purchaseReceipt($mutation),
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

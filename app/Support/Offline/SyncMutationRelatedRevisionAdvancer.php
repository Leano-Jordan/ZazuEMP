<?php

namespace App\Support\Offline;

use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SyncMutationRelatedRevisionAdvancer
{
    public function advance(SyncMutation $mutation): void
    {
        $payload = $mutation->payload['record'] ?? [];

        if ($mutation->entity_type === 'quote_acceptance') {
            $this->advanceAcceptedQuoteWork($mutation, $payload);
            return;
        }

        if ($mutation->entity_type === 'purchase_receipt') {
            $this->advanceReceivedInventory($mutation, $payload);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function advanceAcceptedQuoteWork(SyncMutation $mutation, array $payload): void
    {
        $quoteUuid = $payload['quote_local_id'] ?? null;
        if (! is_string($quoteUuid) || ! Str::isUuid($quoteUuid)) {
            return;
        }

        $quoteIdentity = $this->identityByUuid($mutation->business_id, 'quote', $quoteUuid);
        $quote = $quoteIdentity ? $this->safeRecord($quoteIdentity) : null;

        if ($quote instanceof Quote && is_numeric($quote->event_id)) {
            $this->incrementRecordIdentity(
                (int) $mutation->business_id,
                'job',
                (new Event)->getMorphClass(),
                (int) $quote->event_id,
            );
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function advanceReceivedInventory(SyncMutation $mutation, array $payload): void
    {
        $orderUuid = $payload['purchase_order_local_id'] ?? null;
        if (! is_string($orderUuid) || ! Str::isUuid($orderUuid)) {
            return;
        }

        $orderIdentity = $this->identityByUuid($mutation->business_id, 'purchase_order', $orderUuid);
        $order = $orderIdentity ? $this->safeRecord($orderIdentity) : null;

        if (! $order instanceof PurchaseOrder) {
            return;
        }

        $lineIds = $this->resolvedPurchaseOrderLineIds(
            (int) $mutation->business_id,
            (int) $order->id,
            $payload['received_quantities'] ?? [],
        );
        if ($lineIds === []) {
            return;
        }

        $inventoryItemIds = DB::table('inventory_movements')
            ->where('business_id', $mutation->business_id)
            ->where('purchase_order_id', $order->id)
            ->whereIn('purchase_order_item_id', $lineIds)
            ->distinct()
            ->pluck('inventory_item_id');

        foreach ($inventoryItemIds as $inventoryItemId) {
            $this->incrementRecordIdentity(
                (int) $mutation->business_id,
                'inventory_item',
                (new InventoryItem)->getMorphClass(),
                (int) $inventoryItemId,
            );
        }
    }

    /**
     * @param mixed $receivedQuantities
     * @return array<int, int>
     */
    private function resolvedPurchaseOrderLineIds(int $businessId, int $purchaseOrderId, mixed $receivedQuantities): array
    {
        if (! is_array($receivedQuantities)) {
            return [];
        }

        $ids = [];
        foreach (array_keys($receivedQuantities) as $lineIdentityOrId) {
            if (is_string($lineIdentityOrId) && Str::isUuid($lineIdentityOrId)) {
                $identity = SyncEntityIdentity::query()
                    ->where('business_id', $businessId)
                    ->where('entity_type', 'purchase_order_item')
                    ->where('record_type', (new \\App\\Models\\PurchaseOrderItem)->getMorphClass())
                    ->where('entity_uuid', Str::lower($lineIdentityOrId))
                    ->first();
                if ($identity) {
                    $ids[] = (int) $identity->record_id;
                }
                continue;
            }

            if (is_numeric($lineIdentityOrId) && (int) $lineIdentityOrId > 0) {
                $ids[] = (int) $lineIdentityOrId;
            }
        }

        if ($ids === []) {
            return [];
        }

        return DB::table('purchase_order_items')
            ->where('business_id', $businessId)
            ->where('purchase_order_id', $purchaseOrderId)
            ->whereIn('id', array_values(array_unique($ids)))
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function identityByUuid(int $businessId, string $entityType, string $uuid): ?SyncEntityIdentity
    {
        return SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', $entityType)
            ->where('record_type', $this->expectedMorphType($entityType))
            ->where('entity_uuid', Str::lower($uuid))
            ->lockForUpdate()
            ->first();
    }

    private function safeRecord(SyncEntityIdentity $identity): ?\\Illuminate\\Database\\Eloquent\\Model
    {
        if ($identity->record_type !== $this->expectedMorphType($identity->entity_type)) {
            return null;
        }

        $record = $identity->record;
        if (! $record instanceof \\Illuminate\\Database\\Eloquent\\Model) {
            return null;
        }

        $businessId = $record->getAttribute('business_id');
        if ($businessId === null && method_exists($record, 'event')) {
            $businessId = $record->event()->withTrashed()->value('business_id');
        }

        return is_numeric($businessId) && (int) $businessId === (int) $identity->business_id
            ? $record
            : null;
    }

    private function expectedMorphType(string $entityType): ?string
    {
        $model = match ($entityType) {
            'job' => Event::class,
            'quote' => Quote::class,
            'purchase_order' => PurchaseOrder::class,
            'purchase_order_item' => \\App\\Models\\PurchaseOrderItem::class,
            default => null,
        };

        return $model ? (new $model)->getMorphClass() : null;
    }

    private function incrementRecordIdentity(
        int $businessId,
        string $entityType,
        string $recordType,
        int $recordId,
    ): void {
        $identity = SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', $entityType)
            ->where('record_type', $recordType)
            ->where('record_id', $recordId)
            ->lockForUpdate()
            ->first();

        $identity?->increment('revision');
    }
}

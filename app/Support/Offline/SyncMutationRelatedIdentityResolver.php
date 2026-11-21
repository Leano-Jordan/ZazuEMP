<?php

namespace App\Support\Offline;

use App\Models\Asset;
use App\Models\AssetAllocation;
use App\Models\Customer;
use App\Models\BusinessCapability;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\FinanceExpense;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Payment;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Quote;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SyncMutationRelatedIdentityResolver
{
    /**
     * @return array<int, array{entity_type:string,local_id:string,server_id:int,revision:int}>
     */
    public function resolve(SyncMutation $mutation): array
    {
        $identities = [];
        $primary = $this->primaryIdentity($mutation);

        if ($primary) {
            $this->append($identities, $primary);
        }

        if ($mutation->entity_type === 'quote_acceptance' && $primary) {
            $this->appendAcceptedWork($identities, $primary);
        }

        if ($mutation->entity_type === 'purchase_receipt' && $primary) {
            $this->appendReceivedInventory($identities, $mutation, $primary);
        }

        return array_values($identities);
    }

    private function primaryIdentity(SyncMutation $mutation): ?SyncEntityIdentity
    {
        $payload = $mutation->payload['record'] ?? [];
        $parent = match ($mutation->entity_type) {
            'quote_acceptance' => ['quote', $payload['quote_local_id'] ?? null],
            'payment' => ['invoice', $payload['invoice_local_id'] ?? null],
            'purchase_receipt' => ['purchase_order', $payload['purchase_order_local_id'] ?? null],
            'inventory_movement' => ['inventory_item', $payload['inventory_item_local_id'] ?? null],
            'asset_allocation' => ['asset', $payload['asset_local_id'] ?? null],
            default => null,
        };

        if (! $parent) {
            return null;
        }

        if ($parent[0] === 'asset' && (! is_string($parent[1]) || ! Str::isUuid($parent[1]))) {
            return $this->assetParentIdentity($mutation);
        }

        if (! is_string($parent[1]) || ! Str::isUuid($parent[1])) {
            return null;
        }

        return $this->identityByUuid($mutation, $parent[0], $parent[1]);
    }

    private function appendAcceptedWork(array &$identities, SyncEntityIdentity $quoteIdentity): void
    {
        $quote = $this->safeRecord($quoteIdentity);
        if (! $quote instanceof Quote || ! is_numeric($quote->event_id)) {
            return;
        }

        $eventIdentity = SyncEntityIdentity::query()
            ->where('business_id', $quoteIdentity->business_id)
            ->where('entity_type', 'job')
            ->where('record_type', (new Event)->getMorphClass())
            ->where('record_id', (int) $quote->event_id)
            ->first();

        if ($eventIdentity) {
            $this->append($identities, $eventIdentity);
        }
    }

    private function appendReceivedInventory(
        array &$identities,
        SyncMutation $mutation,
        SyncEntityIdentity $purchaseOrderIdentity,
    ): void {
        $order = $this->safeRecord($purchaseOrderIdentity);
        if (! $order instanceof PurchaseOrder) {
            return;
        }

        $lineIds = $this->resolvedPurchaseOrderLineIds(
            (int) $mutation->business_id,
            (int) $order->id,
            $mutation->payload['record']['received_quantities'] ?? [],
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

        if ($inventoryItemIds->isEmpty()) {
            return;
        }

        $inventoryIdentities = SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', 'inventory_item')
            ->where('record_type', (new InventoryItem)->getMorphClass())
            ->whereIn('record_id', $inventoryItemIds)
            ->orderBy('record_id')
            ->get();

        foreach ($inventoryIdentities as $identity) {
            $this->append($identities, $identity);
        }
    }

    /**
     * Resolve only purchase-order lines named by this receipt. Returning every
     * inventory item ever linked to the order would advance unrelated client
     * revisions without refreshing their snapshots.
     *
     * @return array<int, int>
     */
    private function resolvedPurchaseOrderLineIds(
        int $businessId,
        int $purchaseOrderId,
        mixed $receivedQuantities,
    ): array {
        if (! is_array($receivedQuantities)) {
            return [];
        }

        $candidateIds = [];
        foreach (array_keys($receivedQuantities) as $lineIdentityOrId) {
            if (is_string($lineIdentityOrId) && Str::isUuid($lineIdentityOrId)) {
                $identity = SyncEntityIdentity::query()
                    ->where('business_id', $businessId)
                    ->where('entity_type', 'purchase_order_item')
                    ->where('record_type', (new PurchaseOrderItem)->getMorphClass())
                    ->where('entity_uuid', Str::lower($lineIdentityOrId))
                    ->first();

                if ($identity) {
                    $candidateIds[] = (int) $identity->record_id;
                }

                continue;
            }

            if (is_numeric($lineIdentityOrId) && (int) $lineIdentityOrId > 0) {
                $candidateIds[] = (int) $lineIdentityOrId;
            }
        }

        if ($candidateIds === []) {
            return [];
        }

        return DB::table('purchase_order_items')
            ->where('business_id', $businessId)
            ->where('purchase_order_id', $purchaseOrderId)
            ->whereIn('id', array_values(array_unique($candidateIds)))
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function identityByUuid(SyncMutation $mutation, string $entityType, string $uuid): ?SyncEntityIdentity
    {
        return SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', $entityType)
            ->where('record_type', $this->expectedMorphType($entityType))
            ->where('entity_uuid', Str::lower($uuid))
            ->first();
    }

    private function assetParentIdentity(SyncMutation $mutation): ?SyncEntityIdentity
    {
        $allocationIdentity = $this->identityByUuid($mutation, 'asset_allocation', $mutation->entity_id);
        $allocation = $allocationIdentity ? $this->safeRecord($allocationIdentity) : null;

        if (! $allocation instanceof \App\Models\AssetAllocation) {
            return null;
        }

        return SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', 'asset')
            ->where('record_type', (new Asset)->getMorphClass())
            ->where('record_id', $allocation->asset_id)
            ->first();
    }

    public function isSafeIdentity(SyncEntityIdentity $identity): bool
    {
        return $this->safeRecord($identity) !== null;
    }

    private function safeRecord(SyncEntityIdentity $identity): ?Model
    {
        $expectedType = $this->expectedMorphType($identity->entity_type);
        if ($expectedType === null || $identity->record_type !== $expectedType) {
            return null;
        }

        $record = $identity->record;
        if (! $record instanceof Model) {
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
            'service' => BusinessCapability::class,
            'customer' => Customer::class,
            'job' => Event::class,
            'quote' => Quote::class,
            'invoice' => Invoice::class,
            'payment' => Payment::class,
            'purchase_order' => PurchaseOrder::class,
            'purchase_order_item' => PurchaseOrderItem::class,
            'inventory_item' => InventoryItem::class,
            'asset' => Asset::class,
            'asset_allocation' => AssetAllocation::class,
            'supplier' => Supplier::class,
            'preparation' => EventPreparationItem::class,
            'expense' => FinanceExpense::class,
            'event_cost' => EventCost::class,
            default => null,
        };

        return $model ? (new $model)->getMorphClass() : null;
    }
    /**
     * @param array<string, array{entity_type:string,local_id:string,server_id:int,revision:int}> $identities
     */
    private function append(array &$identities, SyncEntityIdentity $identity): void
    {
        if (! $this->safeRecord($identity)) {
            return;
        }

        $key = $identity->entity_type.':'.$identity->entity_uuid;
        $identities[$key] = [
            'entity_type' => $identity->entity_type,
            'local_id' => $identity->entity_uuid,
            'server_id' => (int) $identity->record_id,
            'revision' => (int) $identity->revision,
        ];
    }
}

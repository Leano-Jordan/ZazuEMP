<?php

namespace App\Support\Offline;

use App\Models\Asset;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
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
        $quote = $quoteIdentity->record;
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
        $order = $purchaseOrderIdentity->record;
        if (! $order instanceof PurchaseOrder) {
            return;
        }

        $inventoryItemIds = DB::table('inventory_movements')
            ->where('business_id', $mutation->business_id)
            ->where('purchase_order_id', $order->id)
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

    private function identityByUuid(SyncMutation $mutation, string $entityType, string $uuid): ?SyncEntityIdentity
    {
        return SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', $entityType)
            ->where('entity_uuid', Str::lower($uuid))
            ->first();
    }

    private function assetParentIdentity(SyncMutation $mutation): ?SyncEntityIdentity
    {
        $allocationIdentity = $this->identityByUuid($mutation, 'asset_allocation', $mutation->entity_id);
        $allocation = $allocationIdentity?->record;

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

    /**
     * @param array<string, array{entity_type:string,local_id:string,server_id:int,revision:int}> $identities
     */
    private function append(array &$identities, SyncEntityIdentity $identity): void
    {
        $key = $identity->entity_type.':'.$identity->entity_uuid;
        $identities[$key] = [
            'entity_type' => $identity->entity_type,
            'local_id' => $identity->entity_uuid,
            'server_id' => (int) $identity->record_id,
            'revision' => (int) $identity->revision,
        ];
    }
}

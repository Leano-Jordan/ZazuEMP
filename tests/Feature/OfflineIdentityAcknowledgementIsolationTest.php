<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAllocation;
use App\Models\Business;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\SyncEntityIdentity;
use App\Support\Offline\SyncEntitySnapshotBuilder;
use App\Support\Offline\SyncMutationRelatedIdentityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineIdentityAcknowledgementIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_identity_guard_accepts_a_valid_inventory_movement_identity(): void
    {
        $business = $this->business('Inventory movement identity business');
        $item = InventoryItem::create([
            'business_id' => $business->id,
            'sku' => 'INV-MOVE-001',
            'name' => 'Test stock',
            'unit' => 'kg',
            'reorder_level' => '0.00',
        ]);
        $movement = InventoryMovement::create([
            'business_id' => $business->id,
            'inventory_item_id' => $item->id,
            'idempotency_key' => (string) Str::uuid(),
            'type' => 'receipt',
            'quantity' => '2.00',
            'movement_date' => now()->toDateString(),
        ]);
        $identity = SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'inventory_movement',
            'entity_uuid' => (string) Str::uuid(),
            'record_type' => $movement->getMorphClass(),
            'record_id' => $movement->id,
        ]);

        $this->assertTrue(app(SyncMutationRelatedIdentityResolver::class)->isSafeIdentity($identity));
    }

    public function test_sync_snapshot_does_not_return_a_foreign_related_asset_identity(): void
    {
        $business = $this->business('Asset snapshot business');
        $foreignBusiness = $this->business('Foreign asset owner');
        $foreignAsset = Asset::create([
            'business_id' => $foreignBusiness->id,
            'asset_tag' => 'FOREIGN-ASSET-001',
            'name' => 'Foreign asset',
            'status' => 'available',
            'condition' => 'good',
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'ASSET-SNAPSHOT-001',
            'name' => 'Local allocation event',
            'event_type' => 'Other',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);
        $allocation = AssetAllocation::create([
            'business_id' => $business->id,
            'asset_id' => $foreignAsset->id,
            'event_id' => $event->id,
            'allocated_from' => now()->toDateString(),
            'status' => 'allocated',
        ]);
        $allocationIdentity = SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'asset_allocation',
            'entity_uuid' => (string) Str::uuid(),
            'record_type' => $allocation->getMorphClass(),
            'record_id' => $allocation->id,
        ]);
        SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'asset',
            'entity_uuid' => (string) Str::uuid(),
            'record_type' => $foreignAsset->getMorphClass(),
            'record_id' => $foreignAsset->id,
        ]);

        $snapshot = app(SyncEntitySnapshotBuilder::class)->build($allocationIdentity);

        $this->assertSame([], $snapshot['related']);
    }

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }
}

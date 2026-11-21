<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\SyncEntityIdentity;
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

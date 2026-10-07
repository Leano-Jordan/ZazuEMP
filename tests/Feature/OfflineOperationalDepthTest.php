<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\SyncDevice;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfflineOperationalDepthTest extends TestCase
{
    use RefreshDatabase;

    private function business(): Business
    {
        return Business::create([
            'name' => 'Offline Operations Business',
            'slug' => 'offline-operations-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }

    private function device(Business $business): SyncDevice
    {
        return SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Offline test phone',
            'device_type' => 'phone',
        ]);
    }

    public function test_offline_inventory_item_and_movement_are_business_scoped_and_idempotent(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-STOCK-001',
            'name' => 'Offline Stock Job',
            'status' => 'confirmed',
        ]);

        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);
        $eventUuid = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($event, 'job')->entity_uuid;

        $itemUuid = (string) Str::uuid();
        $itemMutation = $recorder->record($device, 'inventory_item', $itemUuid, 'create', [
            'local_id' => $itemUuid,
            'record' => [
                'name' => 'Serving Trays',
                'sku' => 'TRAY-001',
                'unit' => 'unit',
                'reorder_level' => '2.00',
            ],
        ]);
        $applier->apply($itemMutation, ['inventory_item' => $handler]);

        $item = InventoryItem::query()->where('business_id', $business->id)->firstOrFail();
        $itemIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($item, 'inventory_item')->entity_uuid;

        $movementMutation = $recorder->record($device, 'inventory_movement', (string) Str::uuid(), 'create', [
            'local_id' => (string) Str::uuid(),
            'record' => [
                'inventory_item_local_id' => $itemIdentity,
                'event_local_id' => $eventUuid,
                'type' => 'receipt',
                'quantity' => '10.00',
                'unit_cost' => '25.50',
                'movement_date' => now()->toDateString(),
                'reference' => 'OFF-RECEIPT-001',
            ],
        ]);
        $applier->apply($movementMutation, ['inventory_movement' => $handler]);
        $applier->apply($movementMutation, ['inventory_movement' => $handler]);

        $this->assertSame(10.0, $item->fresh()->on_hand);
        $this->assertSame(1, InventoryMovement::query()->where('business_id', $business->id)->count());
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'inventory.movement.recorded',
        ]);
    }

    public function test_offline_inventory_issue_cannot_make_stock_negative(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $item = InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Fuel',
            'unit' => 'litre',
            'reorder_level' => '1.00',
        ]);
        $registry = app(\App\Support\Offline\SyncEntityIdentityRegistry::class);
        $itemUuid = $registry->identify($item, 'inventory_item')->entity_uuid;

        $movement = InventoryMovement::create([
            'business_id' => $business->id,
            'inventory_item_id' => $item->id,
            'idempotency_key' => (string) Str::uuid(),
            'type' => 'receipt',
            'quantity' => '5.00',
            'unit_cost' => '10.00',
            'movement_date' => now()->toDateString(),
        ]);

        $mutation = app(SyncMutationRecorder::class)->record($device, 'inventory_movement', (string) Str::uuid(), 'create', [
            'local_id' => (string) Str::uuid(),
            'record' => [
                'inventory_item_local_id' => $itemUuid,
                'type' => 'issue',
                'quantity' => '6.00',
                'unit_cost' => '0.00',
                'movement_date' => now()->toDateString(),
            ],
        ]);

        $this->expectException(ValidationException::class);
        app(SyncMutationApplier::class)->apply($mutation, [
            'inventory_movement' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);

        $this->assertSame(5.0, $item->fresh()->on_hand);
        $this->assertCount(1, InventoryMovement::query()->where('business_id', $business->id)->get());
    }

    public function test_offline_event_cost_requires_valid_financial_state(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-COST-001',
            'name' => 'Offline Cost Job',
            'status' => 'confirmed',
        ]);
        $eventUuid = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($event, 'job')->entity_uuid;
        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);

        $costUuid = (string) Str::uuid();
        $mutation = $recorder->record($device, 'event_cost', $costUuid, 'create', [
            'local_id' => $costUuid,
            'record' => [
                'event_local_id' => $eventUuid,
                'category' => 'Food',
                'description' => 'Offline ingredient cost',
                'currency' => 'ZAR',
                'projected_amount' => '500.00',
                'actual_amount' => '500.00',
                'status' => 'incurred',
                'notes' => 'Reconciled offline.',
            ],
        ]);

        $applier->apply($mutation, ['event_cost' => $handler]);

        $cost = EventCost::query()->where('business_id', $business->id)->firstOrFail();
        $this->assertSame('500.00', $cost->actual_amount);
        $this->assertSame($event->id, $cost->event_id);

        $badMutation = $recorder->record($device, 'event_cost', (string) Str::uuid(), 'create', [
            'local_id' => (string) Str::uuid(),
            'record' => [
                'event_local_id' => $eventUuid,
                'category' => 'Food',
                'description' => 'Invalid planned cost',
                'currency' => 'ZAR',
                'projected_amount' => '500.00',
                'actual_amount' => '10.00',
                'status' => 'planned',
            ],
        ]);

        $this->expectException(ValidationException::class);
        $applier->apply($badMutation, ['event_cost' => $handler]);
    }

    public function test_offline_asset_allocation_and_release_preserve_business_boundaries(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-ASSET-001',
            'name' => 'Offline Asset Job',
            'status' => 'confirmed',
        ]);
        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'OFF-ASSET-001',
            'name' => 'Portable PA',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '5000.00',
            'currency' => 'ZAR',
        ]);

        $registry = app(\App\Support\Offline\SyncEntityIdentityRegistry::class);
        $assetUuid = $registry->identify($asset, 'asset')->entity_uuid;
        $eventUuid = $registry->identify($event, 'job')->entity_uuid;
        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);

        $allocationUuid = (string) Str::uuid();
        $create = $recorder->record($device, 'asset_allocation', $allocationUuid, 'create', [
            'local_id' => $allocationUuid,
            'record' => [
                'asset_local_id' => $assetUuid,
                'event_local_id' => $eventUuid,
                'allocated_from' => now()->toDateString(),
                'allocated_until' => now()->addDay()->toDateString(),
            ],
        ]);
        $applier->apply($create, ['asset_allocation' => $handler]);

        $this->assertSame('allocated', $asset->fresh()->status);
        $this->assertSame(1, $asset->allocations()->where('status', 'allocated')->count());
        $allocation = $asset->allocations()->firstOrFail();
        $allocationIdentity = $registry->identify($allocation, 'asset_allocation')->entity_uuid;

        $release = $recorder->record($device, 'asset_allocation', $allocationIdentity, 'update', [
            'local_id' => $allocationIdentity,
            'record' => [
                'asset_local_id' => $assetUuid,
                'status' => 'returned',
            ],
        ]);
        $applier->apply($release, ['asset_allocation' => $handler]);

        $this->assertSame('available', $asset->fresh()->status);
        $this->assertSame('returned', $allocation->fresh()->status);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SyncDelivery;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use App\Support\Offline\SyncMutationProtocol;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationHandler;
use App\Support\Offline\SyncMutationRecorder;
use App\Support\Offline\SyncConflictRecorder;
use Tests\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OfflineSyncFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_device_and_mutation_are_business_scoped_and_replayable(): void
    {
        $business = Business::create([
            'name' => 'Offline Business',
            'slug' => 'offline-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Owner phone',
            'device_type' => 'phone',
        ]);

        $mutation = SyncMutation::create([
            'business_id' => $business->id,
            'sync_device_id' => $device->id,
            'mutation_id' => (string) Str::uuid(),
            'entity_type' => 'event',
            'entity_id' => 'local-123',
            'operation' => 'upsert',
            'payload' => ['name' => 'Offline Event'],
            'occurred_at' => now(),
        ]);

        $this->assertTrue($device->isActive());
        $this->assertSame($business->id, $mutation->business_id);
        $this->assertSame($device->id, $mutation->device->id);
        $this->assertSame('Offline Event', $mutation->payload['name']);
        $this->assertSame('pending', $mutation->status);
    }

    public function test_mutation_ids_are_unique_for_idempotent_replay(): void
    {
        $business = Business::create([
            'name' => 'Idempotency Business',
            'slug' => 'idempotency-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $mutationId = (string) Str::uuid();

        SyncMutation::create([
            'business_id' => $business->id,
            'sync_device_id' => $device->id,
            'mutation_id' => $mutationId,
            'entity_type' => 'event',
            'entity_id' => 'local-1',
            'operation' => 'upsert',
            'occurred_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        SyncMutation::create([
            'business_id' => $business->id,
            'sync_device_id' => $device->id,
            'mutation_id' => $mutationId,
            'entity_type' => 'event',
            'entity_id' => 'local-1',
            'operation' => 'upsert',
            'occurred_at' => now(),
        ]);
    }

    public function test_mutation_recorder_creates_pending_mutations_and_is_idempotent(): void
    {
        $business = Business::create([
            'name' => 'Recorder Business',
            'slug' => 'recorder-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $mutationId = (string) Str::uuid();
        $recorder = app(SyncMutationRecorder::class);

        $first = $recorder->record(
            $device,
            'event',
            'local-event-1',
            'upsert',
            ['name' => 'Offline wedding'],
            $mutationId,
        );

        $second = $recorder->record(
            $device,
            'event',
            'local-event-1',
            'upsert',
            ['name' => 'Offline wedding'],
            $mutationId,
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, SyncMutation::query()->where('mutation_id', $mutationId)->count());
        $this->assertSame('pending', $first->status);
    }

    public function test_mutation_recorder_rejects_revoked_devices(): void
    {
        $business = Business::create([
            'name' => 'Revoked Device Business',
            'slug' => 'revoked-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        $this->expectException(\LogicException::class);

        app(SyncMutationRecorder::class)->record(
            $device,
            'event',
            'local-event-2',
            'upsert',
            ['name' => 'Should not queue'],
        );
    }


    public function test_mutation_recorder_assigns_ordered_stream_sequences(): void
    {
        $business = Business::create([
            'name' => 'Sequence Business',
            'slug' => 'sequence-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $recorder = app(SyncMutationRecorder::class);

        $first = $recorder->record($device, 'event', 'event-1', 'upsert', ['name' => 'First']);
        $second = $recorder->record($device, 'event', 'event-2', 'upsert', ['name' => 'Second']);

        $this->assertSame('business', $first->stream);
        $this->assertSame(1, $first->sequence);
        $this->assertSame(2, $second->sequence);
    }

    public function test_sync_protocol_pulls_in_order_and_acknowledges_without_skipping(): void
    {
        $business = Business::create([
            'name' => 'Protocol Business',
            'slug' => 'protocol-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $sourceDevice = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $destinationDevice = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $recorder = app(SyncMutationRecorder::class);
        $recorder->record($sourceDevice, 'event', 'event-1', 'upsert', ['name' => 'First']);
        $recorder->record($sourceDevice, 'event', 'event-2', 'upsert', ['name' => 'Second']);

        $protocol = app(SyncMutationProtocol::class);
        $batch = $protocol->pull($destinationDevice);

        $this->assertCount(2, $batch);
        $this->assertSame([1, 2], $batch->pluck('sequence')->all());
        $this->assertSame(2, SyncDelivery::query()->where('destination_device_id', $destinationDevice->id)->count());

        $protocol->acknowledgeThrough($destinationDevice, 1);

        $this->assertSame('applied', SyncDelivery::query()->where('destination_device_id', $destinationDevice->id)->where('delivery_sequence', 1)->value('status'));
        $this->assertSame('pending', SyncDelivery::query()->where('destination_device_id', $destinationDevice->id)->where('delivery_sequence', 2)->value('status'));
        $this->assertSame('pending', SyncMutation::query()->where('sequence', 1)->first()->status);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $protocol->acknowledgeThrough($destinationDevice, 3);
    }


    public function test_sync_applier_requires_an_explicit_domain_handler(): void
    {
        $business = Business::create([
            'name' => 'Handler Business',
            'slug' => 'handler-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'event',
            'event-1',
            'upsert',
            ['name' => 'Protected'],
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(SyncMutationApplier::class)->apply($mutation, []);
    }

    public function test_conflict_recorder_rejects_a_device_from_another_business(): void
    {
        $first = Business::create([
            'name' => 'First Conflict Business',
            'slug' => 'first-conflict-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $second = Business::create([
            'name' => 'Second Conflict Business',
            'slug' => 'second-conflict-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $second->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(SyncConflictRecorder::class)->record(
            $first->id,
            'event',
            'event-1',
            'payload_mismatch',
            $device,
        );
    }

    public function test_supported_offline_mutations_apply_to_business_data_idempotently(): void
    {
        $business = Business::create([
            'name' => 'Offline Apply Business',
            'slug' => 'offline-apply-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $mutationId = (string) Str::uuid();
        $localId = (string) Str::uuid();
        $recorder = app(SyncMutationRecorder::class);

        $mutation = $recorder->record(
            $device,
            'customer',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'server_id' => null,
                'record' => [
                    'name' => 'Offline Customer',
                    'phone' => '0123456789',
                    'email' => 'customer@example.test',
                ],
            ],
            $mutationId,
        );

        $applier = app(SyncMutationApplier::class);
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);

        $first = $applier->apply($mutation, ['customer' => $handler]);
        $second = $applier->apply($first, ['customer' => $handler]);

        $this->assertSame('applied', $first->status);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, \App\Models\Customer::query()->where('business_id', $business->id)->where('name', 'Offline Customer')->count());
        $this->assertSame('0123456789', \App\Models\CustomerContact::query()->whereHas('customer', fn ($q) => $q->where('business_id', $business->id))->value('phone'));
    }

    public function test_offline_preparation_create_and_status_update_are_business_scoped_and_idempotent(): void
    {
        $business = Business::create([
            'name' => 'Preparation Business',
            'slug' => 'preparation-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = \App\Models\Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-PREP-001',
            'name' => 'Offline Wedding',
            'customer_name' => 'Test Customer',
            'event_date' => now()->addDays(14)->toDateString(),
            'status' => 'draft',
        ]);
        $eventIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($event, 'job');
        $localId = (string) Str::uuid();
        $recorder = app(SyncMutationRecorder::class);
        $mutation = $recorder->record(
            $device,
            'preparation',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'event_local_id' => $eventIdentity->entity_uuid,
                    'title' => 'Confirm buffet equipment',
                    'category' => 'equipment',
                    'quantity' => 20,
                    'unit' => 'items',
                    'status' => 'ready',
                    'due_date' => now()->addDays(7)->toDateString(),
                ],
            ],
        );

        $applier = app(SyncMutationApplier::class);
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);
        $first = $applier->apply($mutation, ['preparation' => $handler]);
        $second = $applier->apply($first, ['preparation' => $handler]);

        $this->assertSame('applied', $first->status);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, \App\Models\EventPreparationItem::query()
            ->where('business_id', $business->id)
            ->where('event_id', $event->id)
            ->where('title', 'Confirm buffet equipment')
            ->count());
        $this->assertSame('ready', \App\Models\EventPreparationItem::query()->where('event_id', $event->id)->value('status'));
    }

    public function test_offline_preparation_rejects_cross_business_job_identity(): void
    {
        $first = Business::create([
            'name' => 'Preparation First',
            'slug' => 'prep-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Preparation Second',
            'slug' => 'prep-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $first->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $event = \App\Models\Event::create([
            'business_id' => $second->id,
            'reference' => 'OTHER-001',
            'name' => 'Other Business Event',
            'status' => 'draft',
        ]);
        $eventIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($event, 'job');
        $localId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'preparation',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'event_local_id' => $eventIdentity->entity_uuid,
                    'title' => 'Must reject',
                ],
            ],
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(SyncMutationApplier::class)->apply($mutation, [
            'preparation' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);
    }


    public function test_offline_purchase_order_create_is_business_scoped_and_idempotent(): void
    {
        $business = Business::create([
            'name' => 'Offline Purchasing Business',
            'slug' => 'offline-purchasing-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Offline Supplier',
        ]);
        $supplierIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($supplier, 'supplier');
        $localId = (string) Str::uuid();
        $lineLocalId = (string) Str::uuid();

        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'purchase_order',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'supplier_local_id' => $supplierIdentity->entity_uuid,
                    'currency' => 'ZAR',
                    'notes' => 'Offline replenishment',
                    'lines' => [
                        ['local_id' => $lineLocalId, 'description' => 'Folding chairs', 'quantity' => 20, 'unit' => 'units', 'unit_price' => 125.00],
                    ],
                ],
            ],
        );

        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);
        $applier = app(SyncMutationApplier::class);

        $first = $applier->apply($mutation, ['purchase_order' => $handler]);
        $second = $applier->apply($first, ['purchase_order' => $handler]);

        $this->assertSame('applied', $first->status);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, \App\Models\PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->where('reference', 'PO-OFFLINE-'.strtoupper(substr($localId, 0, 8)))
            ->count());
        $this->assertSame('2500.00', \App\Models\PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->value('total_amount'));
        $this->assertSame(1, \App\Models\PurchaseOrderItem::query()
            ->where('business_id', $business->id)
            ->count());
        $this->assertSame(1, \App\Models\SyncEntityIdentity::query()
            ->where('business_id', $business->id)
            ->where('entity_type', 'purchase_order_item')
            ->where('entity_uuid', $lineLocalId)
            ->count());
    }

    public function test_offline_purchase_order_rejects_cross_business_supplier_identity(): void
    {
        $first = Business::create([
            'name' => 'Purchasing First',
            'slug' => 'purchasing-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Purchasing Second',
            'slug' => 'purchasing-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $first->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $second->id,
            'name' => 'Foreign Supplier',
        ]);
        $supplierIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($supplier, 'supplier');
        $localId = (string) Str::uuid();

        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'purchase_order',
            $localId,
            'create',
            [
                'local_id' => $localId,
                'record' => [
                    'supplier_local_id' => $supplierIdentity->entity_uuid,
                    'currency' => 'ZAR',
                    'lines' => [
                        ['description' => 'Should reject', 'quantity' => 1, 'unit_price' => 100],
                    ],
                ],
            ],
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(SyncMutationApplier::class)->apply($mutation, [
            'purchase_order' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);
        $this->assertSame(0, \App\Models\PurchaseOrder::query()->where('business_id', $first->id)->count());
    }

    public function test_offline_purchase_receipt_is_idempotent_and_preserves_business_boundary(): void
    {
        $business = Business::create([
            'name' => 'Offline Receiving Business',
            'slug' => 'offline-receiving-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Receiving Supplier',
        ]);
        $order = \App\Models\PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $supplier->id,
            'idempotency_key' => (string) Str::uuid(),
            'reference' => 'PO-OFFLINE-RECEIVE',
            'status' => 'ordered',
            'currency' => 'ZAR',
            'total_amount' => '500.00',
            'ordered_at' => now()->toDateString(),
        ]);
        $line = \App\Models\PurchaseOrderItem::create([
            'business_id' => $business->id,
            'purchase_order_id' => $order->id,
            'description' => 'Catering trays',
            'quantity' => '10.00',
            'received_quantity' => '0.00',
            'unit' => 'units',
            'unit_price' => '50.00',
            'line_total' => '500.00',
        ]);
        $orderIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($order, 'purchase_order');
        $lineIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($line, 'purchase_order_item');
        $mutationId = (string) Str::uuid();

        $receiptLocalId = (string) Str::uuid();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'purchase_receipt',
            $receiptLocalId,
            'create',
            [
                'local_id' => $receiptLocalId,
                'record' => [
                    'purchase_order_local_id' => $orderIdentity->entity_uuid,
                    'received_quantities' => [$lineIdentity->entity_uuid => '4.00'],
                ],
            ],
            $mutationId,
        );

        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);
        $applier = app(SyncMutationApplier::class);
        $first = $applier->apply($mutation, ['purchase_receipt' => $handler]);
        $second = $applier->apply($first, ['purchase_receipt' => $handler]);

        $this->assertSame('applied', $first->status);
        $this->assertSame($first->id, $second->id);
        $this->assertSame('4.00', $line->fresh()->received_quantity);
        $this->assertSame(1, \App\Models\PurchaseOrderReceipt::query()
            ->where('business_id', $business->id)
            ->where('idempotency_key', $mutationId)
            ->count());
        $this->assertSame(1, \App\Models\InventoryMovement::query()
            ->where('business_id', $business->id)
            ->where('purchase_order_id', $order->id)
            ->count());
    }

    public function test_offline_purchase_order_rejects_reused_line_identity(): void
    {
        $business = Business::create([
            'name' => 'Offline Identity Business',
            'slug' => 'offline-identity-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Identity Supplier',
        ]);
        $supplierIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)->identify($supplier, 'supplier');
        $lineLocalId = (string) Str::uuid();
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);
        $applier = app(SyncMutationApplier::class);

        $first = app(SyncMutationRecorder::class)->record($device, 'purchase_order', (string) Str::uuid(), 'create', [
            'local_id' => (string) Str::uuid(),
            'record' => [
                'supplier_local_id' => $supplierIdentity->entity_uuid,
                'currency' => 'ZAR',
                'status' => 'ordered',
                'lines' => [['local_id' => $lineLocalId, 'description' => 'First line', 'quantity' => 1, 'unit_price' => 10]],
            ],
        ]);
        $applier->apply($first, ['purchase_order' => $handler]);

        $second = app(SyncMutationRecorder::class)->record($device, 'purchase_order', (string) Str::uuid(), 'create', [
            'local_id' => (string) Str::uuid(),
            'record' => [
                'supplier_local_id' => $supplierIdentity->entity_uuid,
                'currency' => 'ZAR',
                'status' => 'ordered',
                'lines' => [['local_id' => $lineLocalId, 'description' => 'Conflicting line', 'quantity' => 1, 'unit_price' => 20]],
            ],
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $applier->apply($second, ['purchase_order' => $handler]);
        $this->assertSame(1, \App\Models\PurchaseOrderItem::query()->where('business_id', $business->id)->count());
    }

    public function test_offline_purchase_order_can_be_created_then_received_by_local_line_identity(): void
    {
        $business = Business::create([
            'name' => 'Offline Chain Business',
            'slug' => 'offline-chain-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Chain Supplier',
        ]);
        $supplierIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($supplier, 'supplier');

        $orderLocalId = (string) Str::uuid();
        $lineLocalId = (string) Str::uuid();

        $create = app(SyncMutationRecorder::class)->record(
            $device,
            'purchase_order',
            $orderLocalId,
            'create',
            [
                'local_id' => $orderLocalId,
                'record' => [
                    'supplier_local_id' => $supplierIdentity->entity_uuid,
                    'currency' => 'ZAR',
                    'status' => 'ordered',
                    'lines' => [[
                        'local_id' => $lineLocalId,
                        'description' => 'Offline chairs',
                        'quantity' => 6,
                        'unit' => 'units',
                        'unit_price' => 100,
                    ]],
                ],
            ],
        );

        $applier = app(SyncMutationApplier::class);
        $handler = app(\App\Support\Offline\OfflineDomainMutationHandler::class);
        $applier->apply($create, ['purchase_order' => $handler]);

        $receipt = app(SyncMutationRecorder::class)->record(
            $device,
            'purchase_receipt',
            (string) Str::uuid(),
            'create',
            [
                'local_id' => (string) Str::uuid(),
                'record' => [
                    'purchase_order_local_id' => $orderLocalId,
                    'received_quantities' => [$lineLocalId => '3.00'],
                ],
            ],
        );

        $applier->apply($receipt, ['purchase_receipt' => $handler]);

        $orderIdentity = \App\Models\SyncEntityIdentity::query()
            ->where('business_id', $business->id)
            ->where('entity_type', 'purchase_order')
            ->where('entity_uuid', $orderLocalId)
            ->firstOrFail();
        $order = \App\Models\PurchaseOrder::findOrFail($orderIdentity->record_id);
        $line = $order->items()->firstOrFail();

        $this->assertSame('3.00', $line->received_quantity);
        $this->assertSame('received', $order->fresh()->status);
        $this->assertSame(1, \App\Models\InventoryMovement::query()
            ->where('business_id', $business->id)
            ->where('purchase_order_id', $order->id)
            ->where('purchase_order_item_id', $line->id)
            ->count());
        $movement = \App\Models\InventoryMovement::query()
            ->where('business_id', $business->id)
            ->where('purchase_order_id', $order->id)
            ->where('purchase_order_item_id', $line->id)
            ->firstOrFail();
        $this->assertSame('3.00', $movement->quantity);
        $this->assertSame('100.00', $movement->unit_cost);
    }

}

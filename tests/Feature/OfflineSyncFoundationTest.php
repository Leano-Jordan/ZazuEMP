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


}

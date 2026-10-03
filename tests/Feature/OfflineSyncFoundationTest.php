<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

        $this->expectException(IlluminateDatabaseQueryException::class);

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
}

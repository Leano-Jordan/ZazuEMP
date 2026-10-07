<?php

namespace Tests\Feature;

use App\Http\Controllers\SyncController;
use App\Models\Business;
use App\Models\SyncConflict;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncConflictResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_conflicts_are_device_scoped_and_can_be_retried_or_discarded(): void
    {
        $first = Business::create([
            'name' => 'Conflict First',
            'slug' => 'conflict-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Conflict Second',
            'slug' => 'conflict-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $first->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Conflict phone',
            'device_type' => 'phone',
            'status' => 'active',
        ]);
        $otherDevice = SyncDevice::create([
            'business_id' => $first->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Other phone',
            'device_type' => 'phone',
            'status' => 'active',
        ]);
        $foreignDevice = SyncDevice::create([
            'business_id' => $second->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Foreign phone',
            'device_type' => 'phone',
            'status' => 'active',
        ]);

        $retryMutation = SyncMutation::create([
            'business_id' => $first->id,
            'sync_device_id' => $device->id,
            'stream' => 'business',
            'sequence' => 1,
            'mutation_id' => (string) Str::uuid(),
            'entity_type' => 'job',
            'entity_id' => 'job-local-1',
            'operation' => 'create',
            'status' => 'pending',
            'payload' => ['local_id' => 'job-local-1'],
            'occurred_at' => now(),
        ]);
        $discardMutation = SyncMutation::create([
            'business_id' => $first->id,
            'sync_device_id' => $device->id,
            'stream' => 'business',
            'sequence' => 2,
            'mutation_id' => (string) Str::uuid(),
            'entity_type' => 'job',
            'entity_id' => 'job-local-2',
            'operation' => 'create',
            'status' => 'pending',
            'payload' => ['local_id' => 'job-local-2'],
            'occurred_at' => now(),
        ]);

        $retryConflict = SyncConflict::create([
            'business_id' => $first->id,
            'sync_device_id' => $device->id,
            'mutation_id' => $retryMutation->mutation_id,
            'entity_type' => 'job',
            'entity_id' => 'job-local-1',
            'conflict_type' => 'domain_rejection',
            'status' => 'open',
            'local_payload' => ['local_id' => 'job-local-1'],
            'remote_payload' => ['message' => 'Rejected'],
        ]);
        $discardConflict = SyncConflict::create([
            'business_id' => $first->id,
            'sync_device_id' => $device->id,
            'mutation_id' => $discardMutation->mutation_id,
            'entity_type' => 'job',
            'entity_id' => 'job-local-2',
            'conflict_type' => 'domain_rejection',
            'status' => 'open',
            'local_payload' => ['local_id' => 'job-local-2'],
            'remote_payload' => ['message' => 'Rejected'],
        ]);
        SyncConflict::create([
            'business_id' => $first->id,
            'sync_device_id' => $otherDevice->id,
            'mutation_id' => (string) Str::uuid(),
            'entity_type' => 'job',
            'entity_id' => 'other-device-job',
            'conflict_type' => 'domain_rejection',
            'status' => 'open',
        ]);
        SyncConflict::create([
            'business_id' => $second->id,
            'sync_device_id' => $foreignDevice->id,
            'mutation_id' => (string) Str::uuid(),
            'entity_type' => 'job',
            'entity_id' => 'foreign-job',
            'conflict_type' => 'domain_rejection',
            'status' => 'open',
        ]);

        $listRequest = Request::create('/api/sync/conflicts', 'GET');
        $listRequest->attributes->set('sync_device', $device);
        $listed = app(SyncController::class)->listConflicts($listRequest)->getData(true);

        $this->assertCount(2, $listed['conflicts']);
        $this->assertEqualsCanonicalizing(
            [$retryConflict->id, $discardConflict->id],
            array_column($listed['conflicts'], 'id'),
        );

        $retryRequest = Request::create('/api/sync/conflicts/'.$retryConflict->id.'/resolve', 'POST', [
            'action' => 'retry',
            'note' => 'Corrected locally before replay.',
        ]);
        $retryRequest->attributes->set('sync_device', $device);
        app(SyncController::class)->resolveConflict($retryRequest, $retryConflict);

        $this->assertSame('resolved', $retryConflict->fresh()->status);
        $this->assertSame('pending', $retryMutation->fresh()->status);
        $this->assertNull($retryMutation->fresh()->last_error);

        $discardRequest = Request::create('/api/sync/conflicts/'.$discardConflict->id.'/resolve', 'POST', [
            'action' => 'discard',
        ]);
        $discardRequest->attributes->set('sync_device', $device);
        app(SyncController::class)->resolveConflict($discardRequest, $discardConflict);

        $this->assertSame('resolved', $discardConflict->fresh()->status);
        $this->assertSame('discarded', $discardMutation->fresh()->status);
    }

    public function test_conflict_cannot_be_resolved_by_another_device(): void
    {
        $business = Business::create([
            'name' => 'Scoped Conflict',
            'slug' => 'scoped-conflict-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $ownerDevice = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Owner phone',
            'device_type' => 'phone',
            'status' => 'active',
        ]);
        $otherDevice = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Other phone',
            'device_type' => 'phone',
            'status' => 'active',
        ]);
        $conflict = SyncConflict::create([
            'business_id' => $business->id,
            'sync_device_id' => $ownerDevice->id,
            'mutation_id' => (string) Str::uuid(),
            'entity_type' => 'job',
            'entity_id' => 'job-local',
            'conflict_type' => 'domain_rejection',
            'status' => 'open',
        ]);

        $request = Request::create('/api/sync/conflicts/'.$conflict->id.'/resolve', 'POST', [
            'action' => 'discard',
        ]);
        $request->attributes->set('sync_device', $otherDevice);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(SyncController::class)->resolveConflict($request, $conflict);
    }
}

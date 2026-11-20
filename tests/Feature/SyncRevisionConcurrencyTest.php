<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\SyncConflict;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use App\Support\Offline\StaleSyncRevisionException;
use App\Support\Offline\SyncConflictResolutionService;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationHandler;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncRevisionConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_online_customer_write_advances_the_registered_aggregate_revision(): void
    {
        [$business, $customer] = $this->customerFixture();
        $identity = app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');

        $this->assertSame(1, (int) $identity->revision);

        $customer->update(['name' => 'Changed on the owner PC']);

        $this->assertSame(2, (int) $identity->fresh()->revision);
    }

    public function test_stale_device_update_is_rejected_and_returns_the_current_remote_snapshot(): void
    {
        [$business, $customer, $device, $identity] = $this->customerFixtureWithDevice();
        $customer->update(['name' => 'Authoritative online version']);

        $mutation = $this->recordCustomerUpdate($device, $identity, 'Stale phone version', [
            'expected_revision' => 1,
        ]);

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'customer' => $this->customerUpdateHandler(),
            ]);
            $this->fail('An update based on an old revision must not overwrite the online version.');
        } catch (StaleSyncRevisionException $exception) {
            $this->assertSame(2, $exception->remotePayload()['revision']);
            $this->assertSame('Authoritative online version', $exception->remotePayload()['record']['name']);
        }

        $this->assertSame('Authoritative online version', $customer->fresh()->name);
        $this->assertSame('pending', $mutation->fresh()->status);
    }

    public function test_same_device_updates_chain_to_the_previous_result_and_detect_intervening_writes(): void
    {
        [$business, $customer, $device, $identity] = $this->customerFixtureWithDevice();
        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = $this->customerUpdateHandler();

        $first = $this->recordCustomerUpdate($device, $identity, 'Phone edit one', [
            'expected_revision' => 1,
        ]);
        $first = $applier->apply($first, ['customer' => $handler]);
        $this->assertSame(2, (int) $first->result_revision);

        $second = $this->recordCustomerUpdate($device, $identity, 'Phone edit two', [
            'predecessor_mutation_id' => $first->mutation_id,
        ]);
        $second = $applier->apply($second, ['customer' => $handler]);
        $this->assertSame(3, (int) $second->result_revision);
        $this->assertSame('Phone edit two', $customer->fresh()->name);

        // A server-side edit after the queued chain invalidates the next edit,
        // even though the browser's own predecessor was successfully applied.
        $customer->update(['name' => 'Intervening owner edit']);

        $third = $this->recordCustomerUpdate($device, $identity, 'Phone edit three', [
            'predecessor_mutation_id' => $second->mutation_id,
        ]);

        try {
            $applier->apply($third, ['customer' => $handler]);
            $this->fail('An intervening online write must break the local causal chain.');
        } catch (StaleSyncRevisionException $exception) {
            $this->assertSame(4, $exception->remotePayload()['revision']);
            $this->assertSame('Intervening owner edit', $exception->remotePayload()['record']['name']);
        }

        $this->assertSame('Intervening owner edit', $customer->fresh()->name);
    }

    public function test_stale_conflict_keep_local_returns_a_new_immutable_mutation_id_and_latest_revision(): void
    {
        [$business, $customer, $device, $identity] = $this->customerFixtureWithDevice();
        $mutation = $this->recordCustomerUpdate($device, $identity, 'Keep this phone edit', [
            'expected_revision' => 1,
        ]);
        $conflict = SyncConflict::create([
            'business_id' => $business->id,
            'sync_device_id' => $device->id,
            'mutation_id' => $mutation->mutation_id,
            'entity_type' => 'customer',
            'entity_id' => $identity->entity_uuid,
            'conflict_type' => 'stale_record_version',
            'status' => 'open',
            'local_payload' => $mutation->payload,
            'remote_payload' => ['revision' => 1, 'record' => ['name' => 'Old snapshot']],
        ]);

        $customer->update(['name' => 'Latest server edit']);

        $service = app(SyncConflictResolutionService::class);
        $response = $service->resolve($device, $conflict, ['action' => 'retry'])->getData(true);

        $this->assertSame('new_mutation_required', $response['mutation_status']);
        $this->assertSame(2, $response['rebase']['expected_revision']);
        $this->assertNotSame($mutation->mutation_id, $response['rebase']['new_mutation_id']);
        $this->assertSame($response['rebase']['new_mutation_id'], $mutation->fresh()->superseded_by_mutation_id);
        $this->assertSame('discarded', $mutation->fresh()->status);
        $this->assertSame('Latest server edit', $response['conflict']['remote_payload']['record']['name']);

        // A retry after a lost response must return the same replacement ID.
        $again = $service->resolve($device, $conflict->fresh(), ['action' => 'retry'])->getData(true);
        $this->assertSame($response['rebase']['new_mutation_id'], $again['rebase']['new_mutation_id']);
    }

    private function customerFixture(): array
    {
        $business = Business::create([
            'name' => 'Revision Test Business',
            'slug' => 'revision-test-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Revision Customer',
        ]);

        return [$business, $customer];
    }

    private function customerFixtureWithDevice(): array
    {
        [$business, $customer] = $this->customerFixture();
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $identity = app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');

        return [$business, $customer, $device, $identity];
    }

    private function recordCustomerUpdate(SyncDevice $device, SyncEntityIdentity $identity, string $name, array $revision): SyncMutation
    {
        return app(SyncMutationRecorder::class)->record(
            $device,
            'customer',
            $identity->entity_uuid,
            'update',
            [
                'local_id' => $identity->entity_uuid,
                ...$revision,
                'record' => ['name' => $name],
            ],
        );
    }

    private function customerUpdateHandler(): SyncMutationHandler
    {
        return new class implements SyncMutationHandler {
            public function apply(SyncMutation $mutation): void
            {
                $identity = SyncEntityIdentity::query()
                    ->where('business_id', $mutation->business_id)
                    ->where('entity_type', 'customer')
                    ->where('entity_uuid', $mutation->payload['local_id'])
                    ->firstOrFail();

                $identity->record->update([
                    'name' => $mutation->payload['record']['name'],
                ]);
            }
        };
    }
}

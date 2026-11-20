<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Supplier;
use App\Models\SyncConflict;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use App\Support\Offline\StaleSyncRevisionException;
use App\Support\Offline\PendingSyncMutationPredecessorException;
use App\Support\Offline\SyncConflictRecorder;
use App\Support\Offline\SyncConflictResolutionService;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationHandler;
use App\Support\Offline\SyncMutationPushProcessor;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncRevisionConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_identities_start_at_one_and_pre_revision_identity_rows_start_at_two(): void
    {
        [$business, $customer] = $this->customerFixture();
        $registry = app(SyncEntityIdentityRegistry::class);
        $newIdentity = $registry->identify($customer, 'customer');

        $legacyCustomer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Pre-revision identity customer',
        ]);
        $legacyIdentity = SyncEntityIdentity::create([
            'business_id' => $business->id,
            'entity_type' => 'customer',
            'entity_uuid' => (string) Str::uuid(),
            'record_type' => $legacyCustomer->getMorphClass(),
            'record_id' => $legacyCustomer->id,
        ]);

        $this->assertSame(1, (int) $newIdentity->revision);
        $this->assertSame(2, (int) $legacyIdentity->revision);
    }

    public function test_online_customer_write_advances_the_registered_aggregate_revision(): void
    {
        [$business, $customer] = $this->customerFixture();
        $identity = app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');

        $this->assertSame(1, (int) $identity->revision);

        $customer->update(['name' => 'Changed on the owner PC']);

        $this->assertSame(2, (int) $identity->fresh()->revision);
    }

    public function test_work_supplier_service_and_asset_online_writes_advance_their_revisions(): void
    {
        [$business, $customer] = $this->customerFixture();
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'REV-WORK-001',
            'name' => 'Revision Work',
            'status' => 'confirmed',
        ]);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Revision Supplier',
        ]);
        $capability = BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Revision Service',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'default_unit' => 'guest',
            'is_active' => true,
        ]);
        $asset = Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'REV-ASSET-001',
            'name' => 'Revision Tent',
            'status' => 'available',
            'condition' => 'good',
            'purchase_cost' => '1000.00',
            'currency' => 'ZAR',
        ]);

        $registry = app(SyncEntityIdentityRegistry::class);
        $identities = [
            'job' => $registry->identify($event, 'job'),
            'supplier' => $registry->identify($supplier, 'supplier'),
            'service' => $registry->identify($capability, 'service'),
            'asset' => $registry->identify($asset, 'asset'),
        ];

        $event->update(['name' => 'Revision Work Updated']);
        $supplier->update(['name' => 'Revision Supplier Updated']);
        $capability->update(['name' => 'Revision Service Updated']);
        $asset->update(['name' => 'Revision Tent Updated']);

        foreach ($identities as $identity) {
            $this->assertSame(2, (int) $identity->fresh()->revision);
        }
    }

    public function test_nested_customer_contact_write_advances_the_parent_snapshot_revision(): void
    {
        [$business, $customer] = $this->customerFixture();
        $identity = app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');

        $customer->contacts()->create([
            'name' => 'Primary contact',
            'phone' => '0123456789',
            'email' => 'primary@example.test',
            'is_primary' => true,
        ]);

        $this->assertSame(2, (int) $identity->fresh()->revision);
    }

    public function test_create_mutation_cannot_overwrite_an_existing_registered_identity(): void
    {
        [$business, $customer, $device, $identity] = $this->customerFixtureWithDevice();
        $mutation = app(SyncMutationRecorder::class)->record(
            $device,
            'customer',
            $identity->entity_uuid,
            'create',
            [
                'local_id' => $identity->entity_uuid,
                'record' => ['name' => 'Attempted create overwrite'],
            ],
        );

        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'customer' => $this->customerUpdateHandler(),
            ]);
            $this->fail('A create mutation must not update a record already bound to its local identity.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('entity_id', $exception->errors());
        }

        $this->assertSame('Revision Customer', $customer->fresh()->name);
        $this->assertSame(1, (int) $identity->fresh()->revision);
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
        $this->assertSame('Keep this phone edit', $mutation->fresh()->payload['record']['name']);
        $this->assertSame('discarded', $mutation->fresh()->status);
        $this->assertSame('Latest server edit', $response['conflict']['remote_payload']['record']['name']);
        $this->assertContains([
            'field' => 'Name',
            'local' => 'Keep this phone edit',
            'remote' => 'Latest server edit',
        ], $response['conflict']['comparison']);

        $replacementId = $response['rebase']['new_mutation_id'];
        $replacement = app(SyncMutationRecorder::class)->record(
            $device,
            'customer',
            $identity->entity_uuid,
            'update',
            [
                'local_id' => $identity->entity_uuid,
                'expected_revision' => $response['rebase']['expected_revision'],
                'record' => ['name' => 'Rebased phone edit'],
            ],
            $replacementId,
        );
        $applier = app(SyncMutationApplier::class);
        $replacement = $applier->apply($replacement, ['customer' => $this->customerUpdateHandler()]);
        $this->assertSame(3, (int) $replacement->result_revision);

        // A later queued edit may still point at the immutable original ID.
        // The server must follow its supersession link to the applied replacement.
        $successor = $this->recordCustomerUpdate($device, $identity, 'Third phone edit', [
            'predecessor_mutation_id' => $mutation->mutation_id,
        ]);
        $successor = $applier->apply($successor, ['customer' => $this->customerUpdateHandler()]);
        $this->assertSame(4, (int) $successor->result_revision);
        $this->assertSame('Third phone edit', $customer->fresh()->name);

        // A retry after a lost response must return the same replacement ID.
        $again = $service->resolve($device, $conflict->fresh(), ['action' => 'retry'])->getData(true);
        $this->assertSame($response['rebase']['new_mutation_id'], $again['rebase']['new_mutation_id']);
    }

    public function test_rejected_first_mutation_does_not_orphan_a_later_same_device_edit_after_rebase(): void
    {
        [$business, $customer, $device, $identity] = $this->customerFixtureWithDevice();
        $recorder = app(SyncMutationRecorder::class);
        $applier = app(SyncMutationApplier::class);
        $handler = $this->customerUpdateHandler();

        $first = $this->recordCustomerUpdate($device, $identity, 'First phone edit', [
            'expected_revision' => 1,
        ]);
        $customer->update(['name' => 'Intervening server edit']);

        $processor = new SyncMutationPushProcessor(
            $recorder,
            $applier,
            app(\App\Support\Offline\OfflineDomainMutationHandler::class),
            app(SyncConflictRecorder::class),
        );
        $firstResult = $processor->process($device, [
            'id' => $first->mutation_id,
            'entity_type' => $first->entity_type,
            'entity_id' => $first->entity_id,
            'operation' => $first->operation,
            'payload' => $first->payload,
        ]);

        $this->assertSame('stale_record_version', $firstResult['conflict_type']);
        $this->assertNotNull($firstResult['conflict_id']);

        $second = $this->recordCustomerUpdate($device, $identity, 'Second phone edit', [
            'predecessor_mutation_id' => $first->mutation_id,
        ]);
        $secondResult = $processor->process($device, [
            'id' => $second->mutation_id,
            'entity_type' => $second->entity_type,
            'entity_id' => $second->entity_id,
            'operation' => $second->operation,
            'payload' => $second->payload,
        ]);

        $this->assertTrue($secondResult['retryable']);
        $this->assertNull($secondResult['conflict_id']);
        $this->assertSame('pending', $secondResult['status']);
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertSame('Intervening server edit', $customer->fresh()->name);

        $conflict = SyncConflict::query()->findOrFail($firstResult['conflict_id']);

        $resolution = app(SyncConflictResolutionService::class)
            ->resolve($device, $conflict, ['action' => 'retry'])
            ->getData(true);

        $this->assertSame('new_mutation_required', $resolution['mutation_status']);
        $this->assertSame(2, $resolution['rebase']['expected_revision']);

        $replacement = $recorder->record(
            $device,
            'customer',
            $identity->entity_uuid,
            'update',
            [
                'local_id' => $identity->entity_uuid,
                'expected_revision' => $resolution['rebase']['expected_revision'],
                'record' => ['name' => 'First phone edit'],
            ],
            $resolution['rebase']['new_mutation_id'],
        );
        $replacement = $applier->apply($replacement, ['customer' => $handler]);
        $this->assertSame(3, (int) $replacement->result_revision);

        $second = $applier->apply($second, ['customer' => $handler]);
        $this->assertSame(4, (int) $second->result_revision);
        $this->assertSame('Second phone edit', $customer->fresh()->name);
        $this->assertSame('First phone edit', $first->fresh()->payload['record']['name']);
        $this->assertSame('Second phone edit', $second->fresh()->payload['record']['name']);
        $this->assertNotSame($first->mutation_id, $replacement->mutation_id);
    }

    public function test_stale_conflict_discard_returns_latest_server_snapshot(): void
    {
        [$business, $customer, $device, $identity] = $this->customerFixtureWithDevice();
        $mutation = $this->recordCustomerUpdate($device, $identity, 'Discard this phone edit', [
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

        $customer->update(['name' => 'Keep server version']);
        $response = app(SyncConflictResolutionService::class)
            ->resolve($device, $conflict, ['action' => 'discard'])
            ->getData(true);

        $this->assertSame('discarded', $response['mutation_status']);
        $this->assertSame(2, $response['conflict']['remote_payload']['revision']);
        $this->assertSame('Keep server version', $response['conflict']['remote_payload']['record']['name']);
        $this->assertSame('discarded', $mutation->fresh()->status);
        $this->assertSame(2, (int) $mutation->fresh()->result_revision);

        $next = $this->recordCustomerUpdate($device, $identity, 'Phone edit after explicit discard', [
            'predecessor_mutation_id' => $mutation->mutation_id,
        ]);
        $applied = app(SyncMutationApplier::class)->apply($next, [
            'customer' => $this->customerUpdateHandler(),
        ]);

        $this->assertSame(3, (int) $applied->result_revision);
        $this->assertSame('Phone edit after explicit discard', $customer->fresh()->name);
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
            'device_name' => 'Revision test phone',
            'device_type' => 'phone',
            'metadata' => ['sync_token_hash' => hash('sha256', 'revision-test-token')],
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
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

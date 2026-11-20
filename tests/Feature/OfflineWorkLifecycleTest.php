<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\SyncDevice;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineWorkLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function business(): Business
    {
        return Business::create([
            'name' => 'Offline Work Lifecycle Business',
            'slug' => 'offline-work-lifecycle-'.Str::lower(Str::random(8)),
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
            'metadata' => ['sync_token_hash' => hash('sha256', 'offline-test-token')],
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
        ]);
    }

    public function test_offline_job_update_reconciles_services_without_losing_existing_requirement_metadata(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-SERVICES-001',
            'name' => 'Offline Services Job',
            'event_type' => 'Other',
            'status' => 'confirmed',
        ]);
        $existing = $event->requirements()->create([
            'description' => 'Catering',
            'category' => 'Food',
            'quantity' => 25,
            'unit' => 'guests',
            'notes' => 'Keep dietary notes',
            'status' => 'ready',
        ]);
        $event->requirements()->create([
            'description' => 'Removed service',
            'category' => 'Other',
            'quantity' => 1,
            'unit' => 'service',
            'status' => 'open',
        ]);
        $identity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($event, 'job')
            ->entity_uuid;

        $mutation = app(SyncMutationRecorder::class)->record($device, 'job', $identity, 'update', [
            'local_id' => $identity,
            'expected_revision' => 1,
            'record' => [
                'name' => 'Offline Services Job',
                'event_type' => 'Other',
                'event_date' => now()->toDateString(),
                'services' => ['Catering', 'Equipment hire'],
            ],
        ]);

        app(SyncMutationApplier::class)->apply($mutation, [
            'job' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);

        $this->assertDatabaseHas('event_requirements', [
            'id' => $existing->id,
            'description' => 'Catering',
            'quantity' => 25,
            'unit' => 'guests',
            'notes' => 'Keep dietary notes',
            'status' => 'ready',
        ]);
        $this->assertDatabaseHas('event_requirements', [
            'event_id' => $event->id,
            'description' => 'Equipment hire',
            'category' => 'Furniture & equipment',
        ]);
        $this->assertDatabaseMissing('event_requirements', [
            'event_id' => $event->id,
            'description' => 'Removed service',
        ]);
        $this->assertSame('applied', $mutation->fresh()->status);
    }

    public function test_offline_job_update_can_reassign_customer_without_quotes(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $oldCustomer = \App\Models\Customer::create([
            'business_id' => $business->id,
            'name' => 'Original Work Customer',
        ]);
        $newCustomer = \App\Models\Customer::create([
            'business_id' => $business->id,
            'name' => 'Replacement Work Customer',
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $oldCustomer->id,
            'reference' => 'OFF-REASSIGN-001',
            'name' => 'Offline Reassignment Job',
            'event_type' => 'Other',
            'status' => 'confirmed',
        ]);
        $customerIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($newCustomer, 'customer')
            ->entity_uuid;
        $jobIdentity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($event, 'job')
            ->entity_uuid;
        $mutation = app(SyncMutationRecorder::class)->record($device, 'job', $jobIdentity, 'update', [
            'local_id' => $jobIdentity,
            'expected_revision' => 1,
            'record' => [
                'name' => 'Offline Reassignment Job',
                'event_type' => 'Other',
                'status' => 'confirmed',
                'customer_local_id' => $customerIdentity,
                'customer_name' => 'Replacement Work Customer',
            ],
        ]);

        app(SyncMutationApplier::class)->apply($mutation, [
            'job' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);

        $this->assertSame($newCustomer->id, $event->fresh()->customer_id);
        $this->assertSame('Replacement Work Customer', $event->fresh()->customer_name);
        $this->assertSame('applied', $mutation->fresh()->status);
    }

    public function test_offline_job_update_can_advance_confirmed_work_to_in_progress(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-STATUS-001',
            'name' => 'Offline Status Job',
            'event_type' => 'Other',
            'status' => 'confirmed',
        ]);
        $identity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($event, 'job')
            ->entity_uuid;
        $mutation = app(SyncMutationRecorder::class)->record($device, 'job', $identity, 'update', [
            'local_id' => $identity,
            'expected_revision' => 1,
            'record' => [
                'name' => 'Offline Status Job',
                'event_type' => 'Other',
                'status' => 'in_progress',
            ],
        ]);

        app(SyncMutationApplier::class)->apply($mutation, [
            'job' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);

        $this->assertSame('in_progress', $event->fresh()->status);
        $this->assertSame('applied', $mutation->fresh()->status);
    }

    public function test_offline_job_cannot_complete_while_preparation_is_open(): void
    {
        $business = $this->business();
        $device = $this->device($business);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFF-COMPLETE-001',
            'name' => 'Offline Completion Job',
            'event_type' => 'Other',
            'status' => 'in_progress',
        ]);
        $event->preparationItems()->create([
            'business_id' => $business->id,
            'title' => 'Confirm final menu',
            'category' => 'Operations',
            'quantity' => 1,
            'unit' => 'task',
            'status' => 'open',
        ]);
        $identity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($event, 'job')
            ->entity_uuid;
        $mutation = app(SyncMutationRecorder::class)->record($device, 'job', $identity, 'update', [
            'local_id' => $identity,
            'expected_revision' => 1,
            'record' => [
                'name' => 'Offline Completion Job',
                'event_type' => 'Other',
                'status' => 'completed',
            ],
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        try {
            app(SyncMutationApplier::class)->apply($mutation, [
                'job' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
            ]);
        } finally {
            $this->assertSame('in_progress', $event->fresh()->status);
            $this->assertSame('pending', $mutation->fresh()->status);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\SyncDevice;
use App\Models\User;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineEventScheduleSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_job_schedule_fields_reach_the_server(): void
    {
        $business = Business::create([
            'name' => 'Offline Schedule Business',
            'slug' => 'offline-schedule-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Schedule test phone',
            'device_type' => 'phone',
            'metadata' => ['sync_token_hash' => hash('sha256', 'offline-schedule-token')],
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFFLINE-SCHEDULE-001',
            'name' => 'Offline multi-day event',
            'event_type' => 'Other',
            'event_date' => '2026-12-11',
            'status' => 'confirmed',
        ]);
        $identity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($event, 'job')
            ->entity_uuid;

        $mutation = app(SyncMutationRecorder::class)->record($device, 'job', $identity, 'update', [
            'local_id' => $identity,
            'record' => [
                'name' => 'Offline multi-day event',
                'event_type' => 'Other',
                'status' => 'confirmed',
                'event_date' => '2026-12-11',
                'event_end_date' => '2026-12-12',
                'event_start_time' => '09:30',
                'event_end_time' => '17:00',
                'setup_date' => '2026-12-10',
                'collection_date' => '2026-12-13',
            ],
        ]);

        app(SyncMutationApplier::class)->apply($mutation, [
            'job' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);

        $saved = $event->fresh();
        $this->assertSame('2026-12-12', $saved->event_end_date->format('Y-m-d'));
        $this->assertSame('09:30', substr((string) $saved->event_start_time, 0, 5));
        $this->assertSame('17:00', substr((string) $saved->event_end_time, 0, 5));
        $this->assertSame('2026-12-10', $saved->setup_date->format('Y-m-d'));
        $this->assertSame('2026-12-13', $saved->collection_date->format('Y-m-d'));
        $this->assertSame('applied', $mutation->fresh()->status);
    }

    public function test_older_queued_job_updates_preserve_schedule_fields_they_do_not_know_about(): void
    {
        $business = Business::create([
            'name' => 'Legacy Offline Schedule Business',
            'slug' => 'legacy-offline-schedule-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Legacy schedule phone',
            'device_type' => 'phone',
            'metadata' => ['sync_token_hash' => hash('sha256', 'legacy-schedule-token')],
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'OFFLINE-SCHEDULE-LEGACY',
            'name' => 'Legacy queued update',
            'event_type' => 'Other',
            'event_date' => '2026-12-11',
            'event_end_date' => '2026-12-12',
            'event_start_time' => '09:30',
            'event_end_time' => '17:00',
            'setup_date' => '2026-12-10',
            'collection_date' => '2026-12-13',
            'status' => 'confirmed',
        ]);
        $identity = app(\App\Support\Offline\SyncEntityIdentityRegistry::class)
            ->identify($event, 'job')
            ->entity_uuid;
        $mutation = app(SyncMutationRecorder::class)->record($device, 'job', $identity, 'update', [
            'local_id' => $identity,
            'record' => [
                'name' => 'Legacy queued update',
                'event_type' => 'Other',
                'status' => 'confirmed',
            ],
        ]);

        app(SyncMutationApplier::class)->apply($mutation, [
            'job' => app(\App\Support\Offline\OfflineDomainMutationHandler::class),
        ]);

        $saved = $event->fresh();
        $this->assertSame('2026-12-12', $saved->event_end_date->format('Y-m-d'));
        $this->assertSame('09:30', substr((string) $saved->event_start_time, 0, 5));
        $this->assertSame('17:00', substr((string) $saved->event_end_time, 0, 5));
        $this->assertSame('2026-12-10', $saved->setup_date->format('Y-m-d'));
        $this->assertSame('2026-12-13', $saved->collection_date->format('Y-m-d'));
    }

}

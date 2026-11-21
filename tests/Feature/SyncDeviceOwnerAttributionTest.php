<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\SyncDevice;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncDeviceOwnerAttributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_approval_is_recorded_for_paired_and_primary_devices(): void
    {
        $business = Business::create([
            'name' => 'Owner Attribution Business',
            'slug' => 'owner-attribution-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($owner)
            ->postJson(route('settings.sync.pairing'), [])
            ->assertOk();

        $pairedDevice = SyncDevice::query()
            ->where('business_id', $business->id)
            ->where('status', 'pending')
            ->firstOrFail();
        $this->assertSame($owner->id, $pairedDevice->approved_by_user_id);

        $installationId = (string) Str::uuid();
        $this->actingAs($owner)
            ->postJson(route('offline.device.register'), [
                'installation_id' => $installationId,
                'device_name' => 'Owner PC',
                'device_type' => 'desktop',
            ])
            ->assertOk();

        $primaryDevice = SyncDevice::query()
            ->where('installation_id', $installationId)
            ->firstOrFail();
        $this->assertSame($owner->id, $primaryDevice->approved_by_user_id);
    }

    public function test_sync_audit_events_use_the_device_approving_owner_as_actor(): void
    {
        $business = Business::create([
            'name' => 'Sync Audit Attribution Business',
            'slug' => 'sync-audit-attribution-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);
        $business->users()->attach($staff->id, ['role' => 'staff']);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'approved_by_user_id' => $owner->id,
            'installation_id' => (string) Str::uuid(),
            'device_type' => 'phone',
            'status' => 'active',
            'token_expires_at' => now()->addDays(30),
            'metadata' => ['sync_token_hash' => hash('sha256', 'sync-attribution-token')],
        ]);

        Auth::login($staff);
        $syncRequest = Request::create('/api/sync/push', 'POST');
        $syncRequest->attributes->set('sync_device', $device);
        $this->app->instance('request', $syncRequest);

        Audit::record('finance.payment.recorded', null, ['amount' => 12345], $business->id);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'action' => 'finance.payment.recorded',
        ]);

        $audit = \App\Models\AuditLog::query()->where('action', 'finance.payment.recorded')->firstOrFail();
        $this->assertSame('owner_approved_sync_device', $audit->metadata['actor_source']);
        $this->assertSame($device->id, $audit->metadata['sync_device_id']);
    }

    public function test_owner_can_record_approval_for_an_active_legacy_device_and_future_sync_audits_use_that_owner(): void
    {
        $business = Business::create([
            'name' => 'Legacy Device Approval Business',
            'slug' => 'legacy-device-approval-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create(['name' => 'Legacy Device Owner']);
        $staff = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Legacy Android',
            'device_type' => 'phone',
            'status' => 'active',
            'token_expires_at' => now()->addDays(30),
            'metadata' => ['sync_token_hash' => hash('sha256', 'legacy-device-token')],
        ]);

        $this->actingAs($owner)
            ->post(route('settings.sync.devices.reapprove', $device))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success');

        $device->refresh();
        $this->assertSame($owner->id, $device->approved_by_user_id);
        $this->assertSame(hash('sha256', 'legacy-device-token'), $device->metadata['sync_token_hash']);

        Auth::login($staff);
        $syncRequest = Request::create('/api/sync/push', 'POST');
        $syncRequest->attributes->set('sync_device', $device);
        $this->app->instance('request', $syncRequest);

        Audit::record('finance.payment.recorded', null, ['amount' => 12500], $business->id);

        $audit = AuditLog::query()->where('action', 'finance.payment.recorded')->firstOrFail();
        $this->assertSame($owner->id, $audit->user_id);
        $this->assertSame('owner_approved_sync_device', $audit->metadata['actor_source']);
        $this->assertSame('Legacy Android', $audit->metadata['sync_device_name']);
        $this->assertSame('phone', $audit->metadata['sync_device_type']);
    }

    public function test_staff_cannot_record_approver_for_a_legacy_device(): void
    {
        $business = Business::create([
            'name' => 'Legacy Device Staff Boundary',
            'slug' => 'legacy-device-staff-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $staff = User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_type' => 'phone',
            'status' => 'active',
            'token_expires_at' => now()->addDays(30),
            'metadata' => ['sync_token_hash' => hash('sha256', 'staff-cannot-approve')],
        ]);

        $this->actingAs($staff)
            ->post(route('settings.sync.devices.reapprove', $device))
            ->assertForbidden();

        $this->assertNull($device->fresh()->approved_by_user_id);
    }

    public function test_activity_audit_identifies_trusted_device_approver_and_keeps_legacy_actor_unknown(): void
    {
        $business = Business::create([
            'name' => 'Audit Attribution Display Business',
            'slug' => 'audit-attribution-display-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create(['name' => 'Recorded Approver']);
        $business->users()->attach($owner->id, ['role' => 'owner']);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'action' => 'finance.payment.recorded',
            'metadata' => [
                'actor_source' => 'owner_approved_sync_device',
                'sync_device_id' => 91,
                'sync_device_name' => 'Owner Android',
                'sync_device_type' => 'phone',
            ],
            'request_id' => 'AUDIT-TRUSTED-DEVICE',
            'created_at' => now(),
        ]);
        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => null,
            'action' => 'finance.expense.recorded',
            'metadata' => [
                'actor_source' => 'owner_approved_sync_device',
                'sync_device_id' => 92,
                'sync_device_name' => 'Old Android',
                'sync_device_type' => 'phone',
            ],
            'request_id' => 'AUDIT-LEGACY-DEVICE',
            'created_at' => now()->subSecond(),
        ]);

        $this->actingAs($owner)
            ->get(route('settings.audit'))
            ->assertOk()
            ->assertSee('Via trusted device')
            ->assertSee('Owner Android')
            ->assertSee('approved by Recorded Approver')
            ->assertSee('approver not recorded (legacy device)');
    }

    public function test_settings_warns_that_trusted_device_changes_are_attributed_to_the_approving_owner(): void
    {
        $business = Business::create([
            'name' => 'Trusted Device Warning Business',
            'slug' => 'trusted-device-warning-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $this->actingAs($owner)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Offline changes from this device are attributed to the approving owner')
            ->assertSee('staff permissions are not re-checked for each offline mutation');
    }

}

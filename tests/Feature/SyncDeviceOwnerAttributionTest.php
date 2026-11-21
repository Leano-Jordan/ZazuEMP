<?php

namespace Tests\Feature;

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
        $business->users()->attach($owner->id, ['role' => 'owner']);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'approved_by_user_id' => $owner->id,
            'installation_id' => (string) Str::uuid(),
            'device_type' => 'phone',
            'status' => 'active',
            'token_expires_at' => now()->addDays(30),
            'metadata' => ['sync_token_hash' => hash('sha256', 'sync-attribution-token')],
        ]);

        Auth::logout();
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
}

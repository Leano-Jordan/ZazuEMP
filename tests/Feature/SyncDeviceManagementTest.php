<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SyncDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncDeviceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_revoke_a_paired_phone_from_business_settings(): void
    {
        $business = Business::create([
            'name' => 'Device Owner Business',
            'slug' => 'device-owner-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create();
        $business->users()->attach($owner->id, ['role' => 'owner']);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Kitchen phone',
            'device_type' => 'phone',
            'status' => 'active',
            'metadata' => ['sync_token_hash' => hash('sha256', 'secret-token')],
        ]);

        $this->actingAs($owner)
            ->post(route('settings.sync.devices.revoke', $device))
            ->assertRedirect(route('settings.index'));

        $device->refresh();
        $this->assertSame('revoked', $device->status);
        $this->assertNotNull($device->revoked_at);
        $this->assertArrayNotHasKey('sync_token_hash', $device->metadata ?? []);

        $this->withToken('secret-token')
            ->getJson('/api/sync/bootstrap')
            ->assertUnauthorized();
    }

    public function test_non_owner_cannot_revoke_a_paired_phone(): void
    {
        $business = Business::create([
            'name' => 'Device Staff Business',
            'slug' => 'device-staff-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $staff = User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
            'metadata' => ['sync_token_hash' => hash('sha256', 'secret-token')],
        ]);

        $this->actingAs($staff)
            ->post(route('settings.sync.devices.revoke', $device))
            ->assertForbidden();

        $this->assertSame('active', $device->fresh()->status);
        $this->assertArrayHasKey('sync_token_hash', $device->fresh()->metadata ?? []);
    }

    public function test_owner_cannot_revoke_a_phone_from_another_business(): void
    {
        $first = Business::create([
            'name' => 'First Device Business',
            'slug' => 'first-device-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Second Device Business',
            'slug' => 'second-device-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = User::factory()->create();
        $first->users()->attach($owner->id, ['role' => 'owner']);

        $foreignDevice = SyncDevice::create([
            'business_id' => $second->id,
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
            'metadata' => ['sync_token_hash' => hash('sha256', 'foreign-token')],
        ]);

        $this->actingAs($owner)
            ->post(route('settings.sync.devices.revoke', $foreignDevice))
            ->assertNotFound();

        $this->assertSame('active', $foreignDevice->fresh()->status);
        $this->assertArrayHasKey('sync_token_hash', $foreignDevice->fresh()->metadata ?? []);
    }
}

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
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
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

        $this->withToken('secret-token')
            ->getJson('/api/sync/pull')
            ->assertUnauthorized();

        $this->withToken('secret-token')
            ->postJson('/api/sync/push', ['mutations' => []])
            ->assertUnauthorized();

        $this->withToken('secret-token')
            ->getJson('/api/sync/attachments')
            ->assertUnauthorized();
    }

    public function test_expired_sync_device_token_is_rejected_without_extending_its_lifetime(): void
    {
        $business = Business::create([
            'name' => 'Expired Device Business',
            'slug' => 'expired-device-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $token = Str::random(64);
        $expiredAt = now()->subSecond();
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
            'last_seen_at' => now()->subDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS + 1),
            'token_expires_at' => $expiredAt,
            'metadata' => ['sync_token_hash' => hash('sha256', $token)],
        ]);

        $this->withToken($token)
            ->getJson('/api/sync/attachments')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'The Zazu sync device token has expired. Pair this device again.');

        $this->assertSame($expiredAt->timestamp, $device->fresh()->token_expires_at->timestamp);
    }

    public function test_authenticated_sync_activity_slides_device_token_expiry_forward(): void
    {
        $business = Business::create([
            'name' => 'Active Device Business',
            'slug' => 'active-device-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $token = Str::random(64);
        $now = now()->startOfSecond();
        $initialExpiry = $now->copy()->addDays(30);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
            'last_seen_at' => $now->copy()->subDays(10),
            'token_expires_at' => $initialExpiry,
            'metadata' => ['sync_token_hash' => hash('sha256', $token)],
        ]);

        $this->travelTo($now);
        try {
            $this->withToken($token)
                ->getJson('/api/sync/attachments')
                ->assertOk()
                ->assertJsonCount(0, 'attachments');

            $fresh = $device->fresh();
            $this->assertSame(
                $now->copy()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS)->timestamp,
                $fresh->token_expires_at->timestamp,
            );
            $this->assertSame($now->timestamp, $fresh->last_seen_at->timestamp);
            $this->assertGreaterThan($initialExpiry->timestamp, $fresh->token_expires_at->timestamp);
        } finally {
            $this->travelBack();
        }
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
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
            'metadata' => ['sync_token_hash' => hash('sha256', 'foreign-token')],
        ]);

        $this->actingAs($owner)
            ->post(route('settings.sync.devices.revoke', $foreignDevice))
            ->assertNotFound();

        $this->assertSame('active', $foreignDevice->fresh()->status);
        $this->assertArrayHasKey('sync_token_hash', $foreignDevice->fresh()->metadata ?? []);
    }
}

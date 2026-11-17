<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SyncDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTrustedDeviceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_password_reset_revokes_business_trusted_devices_and_pairing_credentials(): void
    {
        $owner = User::factory()->create([
            'username' => 'trustedresetowner',
            'email' => 'trustedreset@example.com',
            'password' => 'old-password',
        ]);
        $otherOwner = User::factory()->create();

        $ownedBusiness = Business::create([
            'name' => 'Trusted Reset Owned Business',
            'slug' => 'trusted-reset-owned',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $staffBusiness = Business::create([
            'name' => 'Trusted Reset Staff Business',
            'slug' => 'trusted-reset-staff',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $ownedBusiness->users()->attach($owner->id, ['role' => 'owner']);
        $staffBusiness->users()->attach($owner->id, ['role' => 'staff']);
        $staffBusiness->users()->attach($otherOwner->id, ['role' => 'owner']);

        $activeDevice = SyncDevice::create([
            'business_id' => $ownedBusiness->id,
            'installation_id' => (string) \Illuminate\Support\Str::uuid(),
            'device_name' => 'Old owner phone',
            'device_type' => 'phone',
            'status' => 'active',
            'token_expires_at' => now()->addDays(30),
            'metadata' => ['sync_token_hash' => hash('sha256', 'old-owner-token')],
        ]);
        $pendingDevice = SyncDevice::create([
            'business_id' => $ownedBusiness->id,
            'installation_id' => (string) \Illuminate\Support\Str::uuid(),
            'device_name' => 'Pending owner phone',
            'device_type' => 'phone',
            'status' => 'pending',
            'token_expires_at' => null,
            'metadata' => [
                'pairing_code_hash' => 'pending-code-hash',
                'pairing_expires_at' => now()->addMinutes(10)->toIso8601String(),
            ],
        ]);
        $staffBusinessDevice = SyncDevice::create([
            'business_id' => $staffBusiness->id,
            'installation_id' => (string) \Illuminate\Support\Str::uuid(),
            'device_name' => 'Other owner phone',
            'device_type' => 'phone',
            'status' => 'active',
            'token_expires_at' => now()->addDays(30),
            'metadata' => ['sync_token_hash' => hash('sha256', 'other-owner-token')],
        ]);

        $token = Password::createToken($owner);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $owner->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('dashboard'));

        foreach ([$activeDevice, $pendingDevice] as $device) {
            $device->refresh();
            $this->assertSame('revoked', $device->status);
            $this->assertNotNull($device->revoked_at);
            $this->assertNull($device->token_expires_at);
            $this->assertArrayNotHasKey('sync_token_hash', $device->metadata ?? []);
            $this->assertArrayNotHasKey('pairing_code_hash', $device->metadata ?? []);
            $this->assertArrayNotHasKey('pairing_expires_at', $device->metadata ?? []);
        }

        $staffBusinessDevice->refresh();
        $this->assertSame('active', $staffBusinessDevice->status);
        $this->assertNotNull($staffBusinessDevice->token_expires_at);
        $this->assertArrayHasKey('sync_token_hash', $staffBusinessDevice->metadata ?? []);
    }
}

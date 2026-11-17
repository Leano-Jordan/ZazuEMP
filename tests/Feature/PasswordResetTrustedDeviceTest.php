<?php

namespace Tests\Feature;

use App\Models\AuditLog;
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

    public function test_owner_password_reset_revokes_all_owned_business_devices_and_pairings_only(): void
    {
        $owner = User::factory()->create([
            'username' => 'trustedresetowner',
            'email' => 'trustedreset@example.com',
            'password' => 'old-password',
        ]);
        $staffBusiness = Business::create([
            'name' => 'Trusted Reset Staff Business',
            'slug' => 'trusted-reset-staff',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $ownedBusiness = Business::create([
            'name' => 'Trusted Reset Owned Business',
            'slug' => 'trusted-reset-owned',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $secondOwnedBusiness = Business::create([
            'name' => 'Trusted Reset Second Owned Business',
            'slug' => 'trusted-reset-second-owned',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $staffBusiness->users()->attach($owner->id, ['role' => 'staff']);
        $ownedBusiness->users()->attach($owner->id, ['role' => 'owner']);
        $secondOwnedBusiness->users()->attach($owner->id, ['role' => 'owner']);

        $makeDevice = static function (Business $business, string $name, string $status, array $metadata): SyncDevice {
            return SyncDevice::create([
                'business_id' => $business->id,
                'installation_id' => (string) Str::uuid(),
                'device_name' => $name,
                'device_type' => 'desktop',
                'status' => $status,
                'token_expires_at' => $status === 'active' ? now()->addDays(30) : null,
                'metadata' => $metadata,
            ]);
        };

        $activeDevice = $makeDevice($ownedBusiness, 'Old owner PC', 'active', [
            'sync_token_hash' => hash('sha256', 'old-owner-token'),
        ]);
        $pendingDevice = $makeDevice($ownedBusiness, 'Pending owner phone', 'pending', [
            'pairing_code_hash' => 'pending-code-hash',
            'pairing_expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);
        $secondOwnedDevice = $makeDevice($secondOwnedBusiness, 'Second owner device', 'active', [
            'sync_token_hash' => hash('sha256', 'second-owner-token'),
        ]);
        $staffBusinessDevice = $makeDevice($staffBusiness, 'Other business device', 'active', [
            'sync_token_hash' => hash('sha256', 'other-business-token'),
        ]);

        $token = Password::createToken($owner);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $owner->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        $this->assertSame(302, $response->getStatusCode(), $response->getContent());

        foreach ([$activeDevice, $pendingDevice, $secondOwnedDevice] as $device) {
            $device->refresh();
            $this->assertSame('revoked', $device->status);
            $this->assertNotNull($device->revoked_at);
            $this->assertNull($device->token_expires_at);
            $this->assertArrayNotHasKey('sync_token_hash', $device->metadata ?? []);
            $this->assertArrayNotHasKey('pairing_code_hash', $device->metadata ?? []);
            $this->assertArrayNotHasKey('pairing_expires_at', $device->metadata ?? []);
            $this->assertDatabaseHas('audit_logs', [
                'business_id' => $device->business_id,
                'action' => 'sync.device.revoked',
            ]);
        }

        $staffBusinessDevice->refresh();
        $this->assertSame('active', $staffBusinessDevice->status);
        $this->assertNotNull($staffBusinessDevice->token_expires_at);
        $this->assertArrayHasKey('sync_token_hash', $staffBusinessDevice->metadata ?? []);

        $this->assertSame(3, AuditLog::query()
            ->whereIn('business_id', [$ownedBusiness->id, $secondOwnedBusiness->id])
            ->where('action', 'sync.device.revoked')
            ->count());
    }
}

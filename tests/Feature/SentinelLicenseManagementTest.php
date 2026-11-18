<?php

namespace Tests\Feature;

use App\Models\PlatformLicenseCustomer;
use App\Models\User;
use App\Services\SignedOfflineLicenseVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class SentinelLicenseManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $keyFile;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('PHP Sodium is required for signed licence tests.');
        }

        $keypair = sodium_crypto_sign_keypair();
        $this->keyFile = tempnam(sys_get_temp_dir(), 'zazu-founder-key-');
        file_put_contents($this->keyFile, base64_encode(sodium_crypto_sign_secretkey($keypair)));

        Config::set('zazu.platform_admin_email', 'founder@zazu.test');
        Config::set('zazu.licensing.public_key', base64_encode(sodium_crypto_sign_publickey($keypair)));
        Config::set('zazu.licensing.private_key_file', $this->keyFile);
    }

    protected function tearDown(): void
    {
        if (isset($this->keyFile) && is_file($this->keyFile)) {
            unlink($this->keyFile);
        }

        parent::tearDown();
    }

    public function test_founder_can_register_a_customer_business_using_its_stable_reference(): void
    {
        $founder = User::factory()->create(['email' => 'founder@zazu.test']);
        $businessUuid = (string) Str::uuid();

        $this->actingAs($founder)
            ->post(route('admin.sentinel.licence-customers.store'), [
                'business_name' => 'Registered offline customer',
                'business_uuid' => strtoupper($businessUuid),
                'contact_email' => 'owner@example.test',
            ])
            ->assertRedirect(route('admin.sentinel', ['section' => 'licensing']));

        $this->assertDatabaseHas('platform_license_customers', [
            'business_uuid' => $businessUuid,
            'business_name' => 'Registered offline customer',
            'contact_email' => 'owner@example.test',
        ]);
    }

    public function test_founder_can_issue_a_signed_business_licence_from_sentinel(): void
    {
        $founder = User::factory()->create(['email' => 'founder@zazu.test']);
        $customer = PlatformLicenseCustomer::query()->create([
            'business_uuid' => (string) Str::uuid(),
            'business_name' => 'Sentinel licence customer',
            'created_by' => $founder->id,
        ]);

        $response = $this->actingAs($founder)->post(route('admin.sentinel.licences.issue'), [
            'platform_license_customer_id' => $customer->id,
            'plan' => 'advanced',
            'expires_at' => now()->addYear()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('admin.sentinel', ['section' => 'licensing']));
        $response->assertSessionHas('sentinel_issued_license_token', function (string $token) use ($customer): bool {
            $verified = app(SignedOfflineLicenseVerifier::class)->verifyForBusiness($token, $customer->business_uuid);

            return $verified['payload']['business_uuid'] === $customer->business_uuid
                && $verified['payload']['plan'] === 'advanced';
        });
        $response->assertSessionHas('sentinel_issued_license_business', 'Sentinel licence customer');
        $this->assertDatabaseHas('platform_license_customers', [
            'id' => $customer->id,
            'last_status' => 'active',
            'last_plan' => 'advanced',
        ]);
    }

    public function test_business_user_cannot_open_or_issue_from_the_founder_licensing_console(): void
    {
        $user = User::factory()->create(['email' => 'owner@customer.test']);

        $this->actingAs($user)
            ->get(route('admin.sentinel'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.sentinel.licences.issue'), [
                'platform_license_customer_id' => 1,
                'plan' => 'advanced',
                'never_expires' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.sentinel.licence-customers.store'), [
                'business_name' => 'Attempted registration',
                'business_uuid' => (string) Str::uuid(),
            ])
            ->assertForbidden();
    }

    public function test_founder_can_create_a_signed_revocation_for_a_registered_customer(): void
    {
        $founder = User::factory()->create(['email' => 'founder@zazu.test']);
        $licenseId = (string) Str::uuid();
        $customer = PlatformLicenseCustomer::query()->create([
            'business_uuid' => (string) Str::uuid(),
            'business_name' => 'Revocation customer',
            'created_by' => $founder->id,
            'last_license_id' => $licenseId,
            'last_plan' => 'basic',
            'last_status' => 'active',
            'last_starts_at' => now()->subDay(),
            'last_expires_at' => now()->addMonth(),
            'last_issued_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($founder)
            ->post(route('admin.sentinel.licences.revoke', $customer));

        $response->assertRedirect(route('admin.sentinel', ['section' => 'licensing']));
        $response->assertSessionHas('sentinel_issued_license_token', function (string $token) use ($customer, $licenseId): bool {
            $verified = app(SignedOfflineLicenseVerifier::class)->verifyForBusiness($token, $customer->business_uuid);

            return $verified['payload']['status'] === 'revoked'
                && $verified['payload']['license_id'] === $licenseId;
        });
        $this->assertDatabaseHas('platform_license_customers', [
            'id' => $customer->id,
            'last_status' => 'revoked',
            'last_license_id' => $licenseId,
        ]);
    }

    public function test_licence_issuance_fails_closed_when_external_signing_file_is_not_configured(): void
    {
        Config::set('zazu.licensing.private_key_file', '');

        $founder = User::factory()->create(['email' => 'founder@zazu.test']);
        $customer = PlatformLicenseCustomer::query()->create([
            'business_uuid' => (string) Str::uuid(),
            'business_name' => 'Unconfigured business',
            'created_by' => $founder->id,
        ]);

        $this->actingAs($founder)
            ->post(route('admin.sentinel.licences.issue'), [
                'platform_license_customer_id' => $customer->id,
                'plan' => 'basic',
                'never_expires' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}

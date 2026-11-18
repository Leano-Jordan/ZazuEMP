<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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

    public function test_founder_can_issue_a_signed_business_licence_from_sentinel(): void
    {
        $founder = User::factory()->create(['email' => 'founder@zazu.test']);
        $business = Business::query()->create([
            'name' => 'Sentinel licence customer',
            'slug' => 'sentinel-licence-customer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($founder)->post(route('admin.sentinel.licences.issue'), [
            'business_id' => $business->id,
            'plan' => 'advanced',
            'expires_at' => now()->addYear()->format('Y-m-d\\TH:i'),
        ]);

        $response->assertRedirect(route('admin.sentinel', ['section' => 'licensing']));
        $response->assertSessionHas('sentinel_issued_license_token', function (string $token): bool {
            return (bool) preg_match('/^[A-Za-z0-9_-]+\\.[A-Za-z0-9_-]+$/', $token);
        });
        $response->assertSessionHas('sentinel_issued_license_business', 'Sentinel licence customer');
    }

    public function test_business_user_cannot_open_or_issue_from_the_founder_licensing_console(): void
    {
        $user = User::factory()->create(['email' => 'owner@customer.test']);
        $business = Business::query()->create([
            'name' => 'Customer business',
            'slug' => 'customer-business',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.sentinel'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.sentinel.licences.issue'), [
                'business_id' => $business->id,
                'plan' => 'advanced',
                'never_expires' => '1',
            ])
            ->assertForbidden();
    }

    public function test_licence_issuance_fails_closed_when_external_signing_file_is_not_configured(): void
    {
        Config::set('zazu.licensing.private_key_file', '');

        $founder = User::factory()->create(['email' => 'founder@zazu.test']);
        $business = Business::query()->create([
            'name' => 'Unconfigured business',
            'slug' => 'unconfigured-business',
            'status' => 'active',
        ]);

        $this->actingAs($founder)
            ->post(route('admin.sentinel.licences.issue'), [
                'business_id' => $business->id,
                'plan' => 'basic',
                'never_expires' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}

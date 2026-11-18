<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\OfflineLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineLicenseContinuityTest extends TestCase
{
    use RefreshDatabase;

    public function test_expiry_and_revocation_preserve_basic_customer_data_access(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $customer = Customer::query()->create([
            'business_id' => $business->id,
            'name' => 'Commercial continuity customer',
        ]);
        $licenses = app(OfflineLicenseService::class);

        $expired = $licenses->importSignedLicense($business, $this->signedToken($business, [
            'issued_at' => '2026-10-01T00:00:00+00:00',
            'starts_at' => '2026-10-01T00:00:00+00:00',
            'expires_at' => '2026-10-31T23:59:59+00:00',
        ]));

        $this->assertFalse($expired->isActive());
        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSeeText('Commercial continuity customer');

        $revoked = $licenses->importSignedLicense($business, $this->signedToken($business, [
            'status' => 'revoked',
            'issued_at' => '2026-11-01T00:00:00+00:00',
            'starts_at' => '2026-10-01T00:00:00+00:00',
            'expires_at' => '2027-10-31T23:59:59+00:00',
        ]));

        $this->assertFalse($revoked->isActive());
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'business_id' => $business->id,
            'name' => 'Commercial continuity customer',
        ]);
        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSeeText('Commercial continuity customer');
    }


    /** @return array{0: string, 1: string} */
    private function signingKeys(): array
    {
        $keypair = sodium_crypto_sign_seed_keypair(str_repeat('z', SODIUM_CRYPTO_SIGN_SEEDBYTES));
        $secretKey = sodium_crypto_sign_secretkey($keypair);
        $publicKey = sodium_crypto_sign_publickey($keypair);
        config(['zazu.licensing.public_key' => base64_encode($publicKey)]);

        return [$secretKey, $publicKey];
    }

    private function signedToken(\App\Models\Business $business, array $overrides = []): string
    {
        [$secretKey] = $this->signingKeys();
        $payload = array_merge([
            'version' => 1,
            'business_id' => (int) $business->id,
            'license_id' => (string) Str::uuid(),
            'plan' => 'solo',
            'status' => 'active',
            'issued_at' => '2026-10-01T00:00:00+00:00',
            'starts_at' => '2026-10-01T00:00:00+00:00',
            'expires_at' => '2026-10-31T23:59:59+00:00',
            'features' => ['offline_core' => true],
        ], $overrides);
        $bytes = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = sodium_crypto_sign_detached($bytes, $secretKey);

        return $this->base64UrlEncode($bytes).'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

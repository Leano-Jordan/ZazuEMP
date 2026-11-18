<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Services\SignedOfflineLicenseVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessLicensingIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_version_one_numeric_business_id_tokens_are_rejected(): void
    {
        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('PHP Sodium is required for signed licence tests.');
        }

        $business = Business::query()->create([
            'name' => 'Legacy token business',
            'slug' => 'legacy-token-business',
            'status' => 'active',
        ]);
        $keypair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keypair);
        config(['zazu.licensing.public_key' => base64_encode(sodium_crypto_sign_publickey($keypair))]);

        $payload = [
            'version' => 1,
            'business_id' => $business->id,
            'license_id' => (string) Str::uuid(),
            'plan' => 'basic',
            'status' => 'active',
            'starts_at' => '2026-11-01T00:00:00+00:00',
            'issued_at' => '2026-11-01T00:00:00+00:00',
            'expires_at' => null,
            'features' => new \stdClass(),
        ];
        $payloadBytes = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = sodium_crypto_sign_detached($payloadBytes, $secretKey);
        $token = $this->base64UrlEncode($payloadBytes).'.'.$this->base64UrlEncode($signature);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The signed licence payload has invalid fields or belongs to another business.');

        app(SignedOfflineLicenseVerifier::class)->verifyForBusiness($token, $business->licensing_uuid);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public function test_each_business_receives_a_persistent_unique_licensing_uuid(): void
    {
        $first = Business::query()->create([
            'name' => 'First local business',
            'slug' => 'first-local-business',
            'status' => 'active',
        ]);
        $second = Business::query()->create([
            'name' => 'Second local business',
            'slug' => 'second-local-business',
            'status' => 'active',
        ]);

        $this->assertTrue(Str::isUuid($first->licensing_uuid));
        $this->assertTrue(Str::isUuid($second->licensing_uuid));
        $this->assertNotSame($first->licensing_uuid, $second->licensing_uuid);
        $this->assertSame($first->licensing_uuid, $first->fresh()->licensing_uuid);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\OfflineLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OfflineLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_expired_local_license_is_not_valid(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $license = app(OfflineLicenseService::class)->importSignedLicense(
            $business,
            $this->signedToken($business, [
                'starts_at' => '2026-09-01T00:00:00+00:00',
                'expires_at' => '2026-09-30T23:59:59+00:00',
            ]),
        );

        $this->assertFalse($license->isActive(Carbon::parse('2026-10-01 00:00:00')));
    }

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

    public function test_a_valid_signed_license_can_be_imported_without_network_access(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $token = $this->signedToken($business);

        $license = app(OfflineLicenseService::class)->importSignedLicense($business, $token);

        $this->assertSame('active', $license->status);
        $this->assertSame('solo', $license->plan);
        $this->assertTrue($license->features['offline_core']);
        $this->assertTrue($license->metadata['signed']);
        $this->assertTrue($license->isActive(Carbon::parse('2026-10-15 12:00:00')));
        $this->assertSame('Active', $license->statusLabel(Carbon::parse('2026-10-15 12:00:00')));
    }

    public function test_a_tampered_signed_license_is_rejected_without_changing_the_existing_license(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $service = app(OfflineLicenseService::class);
        $existing = $service->importSignedLicense($business, $this->signedToken($business));
        $token = $this->signedToken($business);
        [, $signature] = explode('.', $token, 2);
        $tampered = $this->base64UrlEncode('{"version":1,"business_id":'.$business->id.',"status":"active"}').'.'.$signature;

        try {
            $service->importSignedLicense($business, $tampered);
            $this->fail('A tampered licence must not be imported.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('The licence signature is invalid.', $exception->getMessage());
        }

        $this->assertSame($existing->license_id, $business->fresh()->license->license_id);
    }

    public function test_a_signed_license_for_another_business_is_rejected(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $otherBusinessId = $business->id + 1000;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The signed licence payload has invalid fields or belongs to another business.');

        app(OfflineLicenseService::class)->importSignedLicense(
            $business,
            $this->signedToken($business, ['business_id' => $otherBusinessId]),
        );
    }

    public function test_a_signed_revocation_is_imported_as_inactive(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $license = app(OfflineLicenseService::class)->importSignedLicense(
            $business,
            $this->signedToken($business, ['status' => 'revoked']),
        );

        $this->assertSame('revoked', $license->status);
        $this->assertFalse($license->isActive());
        $this->assertSame('Revoked', $license->statusLabel());
    }

    public function test_business_owner_can_install_a_signed_offline_license_from_settings(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $response = $this->post(route('settings.license.store'), [
            'license_token' => $this->signedToken($business, [
                'issued_at' => '2026-11-10T00:00:00+00:00',
                'starts_at' => '2026-11-01T00:00:00+00:00',
                'expires_at' => '2027-11-09T23:59:59+00:00',
            ]),
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('business_licenses', [
            'business_id' => $business->id,
            'status' => 'active',
            'plan' => 'solo',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'business.license.imported',
        ]);
    }

    public function test_invalid_license_token_is_not_preserved_in_session_input(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $token = 'private-invalid-token-value';

        $this->from(route('settings.index'))
            ->post(route('settings.license.store'), ['license_token' => $token])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors('license_token');

        $this->assertArrayNotHasKey('license_token', session('_old_input', []));
        $this->assertDatabaseMissing('business_licenses', [
            'business_id' => $business->id,
        ]);
    }

    public function test_settings_displays_an_expired_license_as_expired(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        app(OfflineLicenseService::class)->importSignedLicense(
            $business,
            $this->signedToken($business, [
                'issued_at' => '2026-09-01T00:00:00+00:00',
                'starts_at' => '2026-09-01T00:00:00+00:00',
                'expires_at' => '2026-09-30T23:59:59+00:00',
            ]),
        );

        $this->assertSame('Expired', $business->fresh()->license->statusLabel(Carbon::parse('2026-11-10 12:00:00')));
        $this->assertSame('Not yet active', $business->fresh()->license->statusLabel(Carbon::parse('2026-08-01 12:00:00')));

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('SOLO plan · Expired')
            ->assertDontSee('SOLO plan · Active');
    }

    public function test_non_owner_cannot_install_a_business_license(): void
    {
        $owner = $this->signInAsOwner();
        $business = $owner->businesses()->firstOrFail();
        $staff = \App\Models\User::factory()->create();
        $business->users()->attach($staff->id, ['role' => 'staff']);

        $this->actingAs($staff)
            ->post(route('settings.license.store'), [
                'license_token' => 'not-a-valid-token',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('business_licenses', [
            'business_id' => $business->id,
        ]);
    }

    public function test_workspace_without_a_license_resolves_to_basic_free(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $this->assertSame('basic', app(OfflineLicenseService::class)->effectiveTier($business));
    }

    public function test_effective_tier_falls_back_to_basic_when_a_paid_entitlement_expires(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $license = app(OfflineLicenseService::class)->importSignedLicense($business, $this->signedToken($business, [
            'plan' => 'advanced',
            'issued_at' => '2026-10-01T00:00:00+00:00',
            'starts_at' => '2026-10-01T00:00:00+00:00',
            'expires_at' => '2026-10-31T23:59:59+00:00',
        ]));

        $this->assertSame('advanced', $license->effectiveTier(Carbon::parse('2026-10-15 12:00:00')));
        $this->assertSame('basic', $license->effectiveTier(Carbon::parse('2026-11-01 00:00:00')));
    }

    public function test_revoked_and_unknown_tiers_resolve_to_basic(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $service = app(OfflineLicenseService::class);

        $revoked = $service->importSignedLicense($business, $this->signedToken($business, [
            'plan' => 'advanced',
            'status' => 'revoked',
        ]));
        $this->assertSame('basic', $revoked->effectiveTier());

        $unknown = $service->importSignedLicense($business, $this->signedToken($business, [
            'plan' => 'legacy_solo',
            'issued_at' => '2026-10-02T00:00:00+00:00',
        ]));
        $this->assertSame('basic', $unknown->effectiveTier());
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

    public function test_an_older_active_token_cannot_reverse_a_newer_revocation_with_the_same_license_id(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $licenseId = 'b9c7d5f1-2a34-4c56-8d90-1234567890ab';
        $service = app(OfflineLicenseService::class);

        $service->importSignedLicense($business, $this->signedToken($business, [
            'license_id' => $licenseId,
            'status' => 'revoked',
            'issued_at' => '2026-10-10T00:00:00+00:00',
        ]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This licence is older than the entitlement already installed.');

        $service->importSignedLicense($business, $this->signedToken($business, [
            'license_id' => $licenseId,
            'status' => 'active',
            'issued_at' => '2026-10-09T00:00:00+00:00',
        ]));
    }

    public function test_same_signed_license_can_be_imported_idempotently(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $service = app(OfflineLicenseService::class);
        $token = $this->signedToken($business, [
            'license_id' => 'b9c7d5f1-2a34-4c56-8d90-1234567890ab',
            'issued_at' => '2026-10-10T00:00:00+00:00',
        ]);

        $first = $service->importSignedLicense($business, $token);
        $second = $service->importSignedLicense($business, $token);

        $this->assertSame($first->license_id, $second->license_id);
        $this->assertSame($first->metadata['payload_sha256'], $second->metadata['payload_sha256']);
        $this->assertSame(1, $business->license()->count());
    }

    public function test_different_signed_payload_cannot_reuse_the_installed_issue_timestamp(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $service = app(OfflineLicenseService::class);
        $licenseId = 'b9c7d5f1-2a34-4c56-8d90-1234567890ab';
        $issuedAt = '2026-10-10T00:00:00+00:00';

        $service->importSignedLicense($business, $this->signedToken($business, [
            'license_id' => $licenseId,
            'issued_at' => $issuedAt,
            'features' => ['offline_core' => true],
        ]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A different licence payload cannot reuse an installed issue timestamp.');

        $service->importSignedLicense($business, $this->signedToken($business, [
            'license_id' => $licenseId,
            'issued_at' => $issuedAt,
            'features' => ['offline_core' => false],
        ]));
    }

    public function test_incomplete_installed_signed_metadata_blocks_replacement(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $service = app(OfflineLicenseService::class);

        $service->importSignedLicense($business, $this->signedToken($business));
        $business->fresh()->license->forceFill(['metadata' => ['signed' => true]])->save();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The installed signed licence metadata is incomplete; a newer licence cannot safely replace it.');

        $service->importSignedLicense($business, $this->signedToken($business, [
            'issued_at' => '2026-10-02T00:00:00+00:00',
        ]));
    }

    public function test_signed_license_rejects_timestamps_without_an_explicit_timezone(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The signed licence dates are invalid.');

        app(OfflineLicenseService::class)->importSignedLicense(
            $business,
            $this->signedToken($business, ['starts_at' => '2026-10-01T00:00:00']),
        );
    }

    public function test_signed_license_rejects_normalized_invalid_calendar_dates(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The signed licence dates are invalid.');

        app(OfflineLicenseService::class)->importSignedLicense(
            $business,
            $this->signedToken($business, ['expires_at' => '2026-02-30T23:59:59+00:00']),
        );
    }

    public function test_signed_license_import_fails_closed_when_public_key_is_not_configured(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        config(['zazu.licensing.public_key' => '']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Signed licence verification is not configured.');

        app(OfflineLicenseService::class)->importSignedLicense($business, 'not-a-valid-token');
    }

    public function test_an_older_signed_license_cannot_replace_a_newer_renewal(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $service = app(OfflineLicenseService::class);
        $service->importSignedLicense(
            $business,
            $this->signedToken($business, ['issued_at' => '2026-10-10T00:00:00+00:00']),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This licence is older than the entitlement already installed.');

        $service->importSignedLicense(
            $business,
            $this->signedToken($business, ['issued_at' => '2026-10-01T00:00:00+00:00']),
        );
    }

}
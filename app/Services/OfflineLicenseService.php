<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessLicense;
use Illuminate\Support\Carbon;

class OfflineLicenseService
{
    public function __construct(private readonly SignedOfflineLicenseVerifier $verifier)
    {
    }

    public function current(Business $business): ?BusinessLicense
    {
        return $business->license;
    }

    public function isValid(Business $business): bool
    {
        return $this->current($business)?->isActive() ?? false;
    }

    public function importSignedLicense(
        Business $business,
        string $token,
    ): BusinessLicense {
        $verified = $this->verifier->verifyForBusiness($token, (int) $business->id);
        $payload = $verified['payload'];
        $dates = $verified['dates'];
        $fingerprint = $verified['fingerprint'];

        $this->assertNotOlderThanInstalledLicense($business, $dates['issued_at'], $fingerprint);

        return BusinessLicense::updateOrCreate(
            ['business_id' => $business->id],
            [
                'license_id' => $payload['license_id'],
                'plan' => $payload['plan'],
                'status' => $payload['status'],
                'starts_at' => $dates['starts_at'],
                'expires_at' => $dates['expires_at'],
                'features' => $payload['features'] ?: null,
                'metadata' => [
                    'signed' => true,
                    'format_version' => 1,
                    'issued_at' => $dates['issued_at']->toIso8601String(),
                    'payload_sha256' => $fingerprint,
                ],
                'activated_at' => now(),
                'last_local_check_at' => now(),
            ],
        );
    }

    private function assertNotOlderThanInstalledLicense(
        Business $business,
        Carbon $issuedAt,
        string $fingerprint,
    ): void {
        $existing = $business->license()->first();

        if ($existing?->metadata['signed'] !== true) {
            return;
        }

        $existingIssuedAt = $existing->metadata['issued_at'] ?? null;
        if (! is_string($existingIssuedAt)) {
            return;
        }

        $installedAt = Carbon::parse($existingIssuedAt);

        if ($issuedAt->lt($installedAt)) {
            throw new \InvalidArgumentException('This licence is older than the entitlement already installed.');
        }

        if ($issuedAt->equalTo($installedAt)) {
            $installedFingerprint = $existing->metadata['payload_sha256'] ?? null;

            if (is_string($installedFingerprint) && hash_equals($installedFingerprint, $fingerprint)) {
                return;
            }

            throw new \InvalidArgumentException('A different licence payload cannot reuse an installed issue timestamp.');
        }
    }

    public function markLocalCheck(BusinessLicense $license): void
    {
        $license->forceFill(['last_local_check_at' => now()])->save();
    }
}

<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessLicense;
use Illuminate\Support\Carbon;

class OfflineLicenseService
{
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
        SignedOfflineLicenseVerifier $verifier,
    ): BusinessLicense {
        $verified = $verifier->verifyForBusiness($token, (int) $business->id);
        $payload = $verified['payload'];
        $dates = $verified['dates'];

        $this->assertNotOlderThanInstalledLicense($business, $payload, $dates['issued_at']);

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
                ],
                'activated_at' => now(),
                'last_local_check_at' => now(),
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertNotOlderThanInstalledLicense(
        Business $business,
        array $payload,
        Carbon $issuedAt,
    ): void {
        $existing = $business->license()->first();
        $existingIssuedAt = $existing?->metadata['issued_at'] ?? null;

        if ($existing?->metadata['signed'] === true
            && is_string($existingIssuedAt)
            && $existing->license_id !== $payload['license_id']
            && $issuedAt->lte(Carbon::parse($existingIssuedAt))) {
            throw new \InvalidArgumentException('This licence is older than the entitlement already installed.');
        }
    }

    public function markLocalCheck(BusinessLicense $license): void
    {
        $license->forceFill(['last_local_check_at' => now()])->save();
    }
}

<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessLicense;
use Illuminate\Support\Str;

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

    public function issueLocalEntitlement(
        Business $business,
        string $plan,
        \DateTimeInterface $startsAt,
        ?\DateTimeInterface $expiresAt = null,
        array $features = [],
        array $metadata = [],
    ): BusinessLicense {
        return BusinessLicense::updateOrCreate(
            ['business_id' => $business->id],
            [
                'license_id' => (string) Str::uuid(),
                'plan' => $plan,
                'status' => 'active',
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'features' => $features ?: null,
                'metadata' => $metadata ?: null,
                'activated_at' => now(),
                'last_local_check_at' => now(),
            ],
        );
    }

    public function markLocalCheck(BusinessLicense $license): void
    {
        $license->forceFill(['last_local_check_at' => now()])->save();
    }
}
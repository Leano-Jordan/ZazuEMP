<?php

namespace Tests\Feature;

use App\Models\BusinessLicense;
use App\Services\OfflineLicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OfflineLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_business_can_have_a_local_license_without_any_online_service(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $license = app(OfflineLicenseService::class)->issueLocalEntitlement(
            $business,
            'solo',
            Carbon::parse('2026-10-01 00:00:00'),
            Carbon::parse('2026-10-31 23:59:59'),
            ['offline_core' => true],
        );

        $this->assertInstanceOf(BusinessLicense::class, $license);
        $this->assertTrue($license->isActive(Carbon::parse('2026-10-15 12:00:00')));
        $this->assertTrue(app(OfflineLicenseService::class)->isValid($business));
        $this->assertSame('solo', $license->plan);
        $this->assertTrue($license->features['offline_core']);
    }

    public function test_an_expired_local_license_is_not_valid(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        app(OfflineLicenseService::class)->issueLocalEntitlement(
            $business,
            'solo',
            Carbon::parse('2026-09-01 00:00:00'),
            Carbon::parse('2026-09-30 23:59:59'),
        );

        $license = $business->fresh()->license;

        $this->assertFalse($license->isActive(Carbon::parse('2026-10-01 00:00:00')));
    }
}
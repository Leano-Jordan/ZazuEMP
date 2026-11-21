<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\SyncDevice;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class BusinessArchiveTrustedDeviceRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_restore_refuses_a_workspace_with_trusted_device_state_before_importing(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $source = Business::create([
            'name' => 'Paired Archive Source',
            'slug' => 'paired-archive-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        Customer::create([
            'business_id' => $source->id,
            'name' => 'Archive source customer',
        ]);

        $service = app(BusinessArchiveService::class);
        $archive = $service->download($source);
        $target = Business::create([
            'name' => 'Paired Restore Target',
            'slug' => 'paired-restore-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $device = SyncDevice::create([
            'business_id' => $target->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Trusted phone',
            'device_type' => 'mobile',
        ]);

        try {
            try {
                $service->restore($archive, $target);
                $this->fail('A workspace with trusted-device state must never accept a business archive.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('still has offline sync state', $exception->getMessage());
            }
        } finally {
            @unlink($archive);
        }

        $this->assertSame('Paired Restore Target', $target->fresh()->name);
        $this->assertSame(0, Customer::query()->where('business_id', $target->id)->count());
        $this->assertSame(1, SyncDevice::query()->where('business_id', $target->id)->count());
        $this->assertSame('active', $device->fresh()->status);
        $this->assertSame([], Storage::disk('private')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}

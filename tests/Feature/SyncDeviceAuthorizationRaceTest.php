<?php

namespace Tests\\Feature;

use App\\Http\\Controllers\\SyncController;
use App\\Models\\Business;
use App\\Models\\SyncConflict;
use App\\Models\\SyncDevice;
use App\\Support\\Offline\\SyncConflictResolutionService;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Str;
use Symfony\\Component\\HttpKernel\\Exception\\HttpException;
use Tests\\TestCase;

class SyncDeviceAuthorizationRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_revoked_device_snapshot_cannot_resolve_a_conflict(): void
    {
        [$business, $device, $snapshot] = $this->deviceFixture();
        $device->update(['status' => 'revoked', 'revoked_at' => now()]);

        try {
            app(SyncConflictResolutionService::class)->resolve(
                $snapshot,
                new SyncConflict(),
                ['action' => 'discard'],
            );
            $this->fail('A revoked device must not resolve or mutate an offline conflict.');
        } catch (HttpException $exception) {
            $this->assertSame(401, $exception->getStatusCode());
        }
    }

    public function test_revoked_device_snapshot_cannot_upload_attachments(): void
    {
        [$business, $device, $snapshot] = $this->deviceFixture();
        $device->update(['status' => 'revoked', 'revoked_at' => now()]);

        $request = Request::create('/api/sync/attachments', 'POST');
        $request->attributes->set('sync_device', $snapshot);

        try {
            app(SyncController::class)->uploadAttachment($request);
            $this->fail('A revoked device must not upload an attachment using a stale middleware snapshot.');
        } catch (HttpException $exception) {
            $this->assertSame(401, $exception->getStatusCode());
        }
    }

    public function test_replaced_credential_cannot_be_revoked_by_a_stale_authenticated_snapshot(): void
    {
        [$business, $device, $snapshot] = $this->deviceFixture();
        $metadata = $device->metadata ?? [];
        $metadata['sync_token_hash'] = hash('sha256', 'replacement-token');
        $device->update(['metadata' => $metadata]);

        $request = Request::create('/api/sync/revoke', 'POST');
        $request->attributes->set('sync_device', $snapshot);

        try {
            app(SyncController::class)->revoke($request);
            $this->fail('An old bearer token must not revoke a replacement device credential.');
        } catch (HttpException $exception) {
            $this->assertSame(401, $exception->getStatusCode());
        }

        $this->assertSame('active', $device->fresh()->status);
        $this->assertSame(
            hash('sha256', 'replacement-token'),
            $device->fresh()->metadata['sync_token_hash'],
        );
    }

    public function test_attachment_listing_rejects_a_revoked_device_snapshot(): void
    {
        [$business, $device, $snapshot] = $this->deviceFixture();
        $device->update(['status' => 'revoked', 'revoked_at' => now()]);

        $request = Request::create('/api/sync/attachments', 'GET');
        $request->attributes->set('sync_device', $snapshot);

        try {
            app(SyncController::class)->listAttachments($request);
            $this->fail('A revoked device must not read attachment metadata using a stale snapshot.');
        } catch (HttpException $exception) {
            $this->assertSame(401, $exception->getStatusCode());
        }
    }

    /**
     * @return array{Business, SyncDevice, SyncDevice}
     */
    private function deviceFixture(): array
    {
        $business = Business::create([
            'name' => 'Device Authorization Business',
            'slug' => 'device-auth-'.Str::lower(Str::random(10)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
            'token_expires_at' => now()->addDay(),
            'metadata' => ['sync_token_hash' => hash('sha256', 'original-token')],
        ]);

        return [$business, $device, $device->fresh()];
    }
}

<?php

namespace Tests\Feature;

use App\Http\Controllers\SyncController;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Models\SyncDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncAttachmentListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_attachment_listing_is_business_scoped_and_can_filter_by_event(): void
    {
        $first = Business::create([
            'name' => 'Attachment First',
            'slug' => 'attachment-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Attachment Second',
            'slug' => 'attachment-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $firstEvent = Event::create([
            'business_id' => $first->id,
            'reference' => 'ATT-FIRST-001',
            'name' => 'First event',
            'status' => 'draft',
        ]);
        $secondEvent = Event::create([
            'business_id' => $second->id,
            'reference' => 'ATT-SECOND-001',
            'name' => 'Second event',
            'status' => 'draft',
        ]);

        $device = SyncDevice::create([
            'business_id' => $first->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Attachment phone',
            'device_type' => 'phone',
        ]);

        EventAttachment::create([
            'event_id' => $firstEvent->id,
            'business_id' => $first->id,
            'original_name' => 'first.pdf',
            'disk' => 'private',
            'path' => 'jobs/'.$firstEvent->id.'/attachments/first.pdf',
            'mime_type' => 'application/pdf',
            'size' => 12,
            'source' => 'sync',
            'description' => 'First attachment',
            'idempotency_key' => (string) Str::uuid(),
        ]);
        EventAttachment::create([
            'event_id' => $secondEvent->id,
            'business_id' => $second->id,
            'original_name' => 'foreign.pdf',
            'disk' => 'private',
            'path' => 'jobs/'.$secondEvent->id.'/attachments/foreign.pdf',
            'mime_type' => 'application/pdf',
            'size' => 13,
            'source' => 'sync',
            'description' => 'Foreign attachment',
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $request = Request::create('/api/sync/attachments?event_id='.$firstEvent->id, 'GET');
        $request->attributes->set('sync_device', $device);

        $response = app(SyncController::class)->listAttachments($request);
        $payload = $response->getData(true);

        $this->assertCount(1, $payload['attachments']);
        $this->assertSame('first.pdf', $payload['attachments'][0]['original_name']);
        $this->assertSame($firstEvent->id, $payload['attachments'][0]['event_id']);
    }
    public function test_sync_attachment_upload_is_idempotent_for_retries(): void
    {
        Storage::fake('private');

        $business = Business::create([
            'name' => 'Attachment Retry',
            'slug' => 'attachment-retry-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'ATT-RETRY-001',
            'name' => 'Retry event',
            'status' => 'draft',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Retry phone',
            'device_type' => 'phone',
            'status' => 'active',
        ]);

        $idempotencyKey = (string) Str::uuid();
        $makeRequest = function () use ($event, $device, $idempotencyKey): Request {
            $request = Request::create('/api/sync/attachments', 'POST', [
                'event_id' => $event->id,
                'idempotency_key' => $idempotencyKey,
                'description' => 'Retry-safe file',
            ], [], [
                'file' => UploadedFile::fake()->createWithContent('brief.txt', 'offline attachment'),
            ]);
            $request->attributes->set('sync_device', $device);
            return $request;
        };

        $first = app(SyncController::class)->uploadAttachment($makeRequest());
        $second = app(SyncController::class)->uploadAttachment($makeRequest());

        $this->assertSame(201, $first->getStatusCode());
        $this->assertSame(200, $second->getStatusCode());
        $this->assertFalse($first->getData(true)['duplicate']);
        $this->assertTrue($second->getData(true)['duplicate']);
        $this->assertDatabaseCount('event_attachments', 1);
        $stored = EventAttachment::query()->firstOrFail();
        Storage::disk('private')->assertExists($stored->path);
    }

}

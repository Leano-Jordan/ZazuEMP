<?php

namespace Tests\Feature;

use App\Http\Controllers\SyncController;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Models\SyncDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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
}

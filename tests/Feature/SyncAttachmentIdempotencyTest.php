<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Models\SyncDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncAttachmentIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_identical_attachment_retry_returns_the_original_record(): void
    {
        Storage::fake('private');

        [, $event, $token] = $this->pairedBusinessAndEvent();

        $payload = [
            'event_id' => $event->id,
            'idempotency_key' => (string) Str::uuid(),
            'description' => 'Approved client brief',
            'file' => UploadedFile::fake()->createWithContent('brief.txt', 'Accepted event brief'),
        ];

        $first = $this->withToken($token)->postJson('/api/sync/attachments', $payload);

        $first->assertCreated()
            ->assertJsonPath('duplicate', false);

        $attachmentId = $first->json('attachment.id');

        $retry = $this->withToken($token)->postJson('/api/sync/attachments', [
            ...$payload,
            'file' => UploadedFile::fake()->createWithContent('brief.txt', 'Accepted event brief'),
        ]);

        $retry->assertOk()
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('attachment.id', $attachmentId);

        $this->assertSame(1, EventAttachment::query()->count());
        $this->assertCount(1, Storage::disk('private')->allFiles('jobs/'.$event->id.'/attachments'));
    }

    public function test_phone_attachment_list_and_download_are_business_scoped(): void
    {
        Storage::fake('private');

        [$business, $event, $token] = $this->pairedBusinessAndEvent();
        $idempotencyKey = (string) Str::uuid();

        $uploaded = $this->withToken($token)->postJson('/api/sync/attachments', [
            'event_id' => $event->id,
            'idempotency_key' => $idempotencyKey,
            'description' => 'Phone cache contract',
            'file' => UploadedFile::fake()->createWithContent('phone-brief.txt', 'Private phone attachment'),
        ]);

        $uploaded->assertCreated()
            ->assertJsonPath('attachment.idempotency_key', $idempotencyKey)
            ->assertJsonPath('attachment.event_id', $event->id);

        $attachmentId = $uploaded->json('attachment.id');
        $listed = $this->withToken($token)->getJson('/api/sync/attachments?event_id='.$event->id);

        $listed->assertOk()
            ->assertJsonCount(1, 'attachments')
            ->assertJsonPath('attachments.0.id', $attachmentId)
            ->assertJsonPath('attachments.0.idempotency_key', $idempotencyKey)
            ->assertJsonPath('attachments.0.original_name', 'phone-brief.txt')
            ->assertJsonPath('attachments.0.size', strlen('Private phone attachment'));

        $download = $this->withToken($token)->get('/api/sync/attachments/'.$attachmentId);
        $download->assertOk()
            ->assertDownload('phone-brief.txt');

        Storage::disk('private')->assertExists(EventAttachment::query()->findOrFail($attachmentId)->path);

        $foreignBusiness = Business::create([
            'name' => 'Foreign Attachment Business',
            'slug' => 'foreign-attachment-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $foreignEvent = Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'FOREIGN-'.Str::upper(Str::random(8)),
            'name' => 'Foreign attachment job',
            'status' => 'draft',
        ]);
        $foreignPath = 'jobs/'.$foreignEvent->id.'/attachments/foreign.txt';
        Storage::disk('private')->put($foreignPath, 'foreign');

        $foreignAttachment = EventAttachment::create([
            'event_id' => $foreignEvent->id,
            'business_id' => $foreignBusiness->id,
            'original_name' => 'foreign.txt',
            'disk' => 'private',
            'path' => $foreignPath,
            'mime_type' => 'text/plain',
            'size' => 7,
            'source' => 'sync',
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $this->withToken($token)
            ->getJson('/api/sync/attachments?event_id='.$foreignEvent->id)
            ->assertNotFound();

        $this->withToken($token)
            ->get('/api/sync/attachments/'.$foreignAttachment->id)
            ->assertNotFound();
    }

    public function test_reused_attachment_key_rejects_different_content_with_matching_metadata(): void
    {
        Storage::fake('private');

        [, $event, $token] = $this->pairedBusinessAndEvent();

        $idempotencyKey = (string) Str::uuid();
        $first = $this->withToken($token)->postJson('/api/sync/attachments', [
            'event_id' => $event->id,
            'idempotency_key' => $idempotencyKey,
            'description' => 'Approved client brief',
            'file' => UploadedFile::fake()->createWithContent('brief.txt', 'Accepted event brief'),
        ]);

        $first->assertCreated();

        $conflictingRetry = $this->withToken($token)->postJson('/api/sync/attachments', [
            'event_id' => $event->id,
            'idempotency_key' => $idempotencyKey,
            'description' => 'Approved client brief',
            'file' => UploadedFile::fake()->createWithContent('brief.txt', 'Rejected event brief'),
        ]);

        $conflictingRetry->assertStatus(409)
            ->assertJsonPath('message', 'This idempotency key is already assigned to a different attachment. Keep the local file and create a new upload attempt.');

        $this->assertSame(1, EventAttachment::query()->count());
        $this->assertCount(1, Storage::disk('private')->allFiles('jobs/'.$event->id.'/attachments'));
    }

    public function test_reused_attachment_key_cannot_move_an_attachment_to_another_job(): void
    {
        Storage::fake('private');

        [$business, $event, $token] = $this->pairedBusinessAndEvent();
        $otherCustomer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Second Sync Attachment Customer',
        ]);
        $otherEvent = Event::create([
            'business_id' => $business->id,
            'customer_id' => $otherCustomer->id,
            'reference' => 'ZAZ-SYNC-'.Str::upper(Str::random(8)),
            'name' => 'Second Sync Attachment Work',
            'event_type' => 'Catering order',
            'event_date' => now()->addDays(4)->toDateString(),
            'status' => 'draft',
        ]);

        $idempotencyKey = (string) Str::uuid();
        $first = $this->withToken($token)->postJson('/api/sync/attachments', [
            'event_id' => $event->id,
            'idempotency_key' => $idempotencyKey,
            'file' => UploadedFile::fake()->createWithContent('brief.txt', 'Accepted event brief'),
        ]);

        $first->assertCreated();

        $reusedForOtherJob = $this->withToken($token)->postJson('/api/sync/attachments', [
            'event_id' => $otherEvent->id,
            'idempotency_key' => $idempotencyKey,
            'file' => UploadedFile::fake()->createWithContent('brief.txt', 'Accepted event brief'),
        ]);

        $reusedForOtherJob->assertStatus(409);

        $this->assertSame(1, EventAttachment::query()->count());
        $this->assertDatabaseHas('event_attachments', [
            'event_id' => $event->id,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @return array{Business, Event, string}
     */
    private function pairedBusinessAndEvent(): array
    {
        $business = Business::create([
            'name' => 'Sync Attachment Business',
            'slug' => 'sync-attachment-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Sync Attachment Customer',
        ]);

        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-SYNC-'.Str::upper(Str::random(8)),
            'name' => 'Sync Attachment Work',
            'event_type' => 'Catering order',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ]);

        $token = Str::random(64);

        SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Test phone',
            'device_type' => 'phone',
            'status' => 'active',
            'metadata' => ['sync_token_hash' => hash('sha256', $token)],
        ]);

        return [$business, $event, $token];
    }
}

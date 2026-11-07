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

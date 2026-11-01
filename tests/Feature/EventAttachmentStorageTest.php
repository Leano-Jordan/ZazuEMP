<?php

namespace Tests\\Feature;

use App\\Models\\Business;
use App\\Models\\Customer;
use App\\Models\\Event;
use App\\Models\\EventAttachment;
use App\\Models\\User;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Storage;
use Illuminate\\Support\\Str;
use RuntimeException;
use Tests\\TestCase;

class EventAttachmentStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_attachment_row_deletion_keeps_the_private_file_intact(): void
    {
        Storage::fake('private');

        $business = Business::create([
            'name' => 'Attachment Delete Rollback',
            'slug' => 'attachment-delete-rollback-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Attachment Rollback Customer',
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'ATT-DELETE-ROLLBACK-'.Str::upper(Str::random(5)),
            'name' => 'Attachment rollback work',
            'event_type' => 'Catering order',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ]);

        $path = 'jobs/'.$event->id.'/attachments/rollback-probe.txt';
        Storage::disk('private')->put($path, 'preserve this attachment');

        $attachment = Model::withoutEvents(fn () => EventAttachment::create([
            'event_id' => $event->id,
            'business_id' => $business->id,
            'uploaded_by' => $user->id,
            'original_name' => 'rollback-probe.txt',
            'disk' => 'private',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 24,
            'source' => 'upload',
        ]));

        EventAttachment::deleting(function (EventAttachment $deleting) use ($attachment): void {
            if ((int) $deleting->id === (int) $attachment->id) {
                throw new RuntimeException('Simulated attachment metadata delete failure.');
            }
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)->delete(route('work.attachments.destroy', $attachment));
            $this->fail('The simulated metadata deletion failure should be raised.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated attachment metadata delete failure.', $exception->getMessage());
        }

        Storage::disk('private')->assertExists($path);
        $this->assertDatabaseHas('event_attachments', ['id' => $attachment->id]);
    }
}

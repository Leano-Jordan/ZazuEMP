<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class EventAttachmentStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_attachment_row_deletion_keeps_the_private_file_intact(): void
    {
        [, $user, , $attachment, $path] = $this->createAttachmentFixture();

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

    public function test_successful_attachment_deletion_removes_the_row_and_private_file(): void
    {
        [, $user, $event, $attachment, $path] = $this->createAttachmentFixture();

        $response = $this->actingAs($user)->delete(route('work.attachments.destroy', $attachment));

        $response->assertRedirect(route('work.show', $event));
        $this->assertDatabaseMissing('event_attachments', ['id' => $attachment->id]);
        Storage::disk('private')->assertMissing($path);
    }

    /**
     * @return array{0:Business,1:User,2:Event,3:EventAttachment,4:string}
     */
    private function createAttachmentFixture(): array
    {
        Storage::fake('private');

        $business = Business::create([
            'name' => 'Attachment Storage Test',
            'slug' => 'attachment-storage-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Attachment Storage Customer',
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'ATT-STORAGE-'.Str::upper(Str::random(6)),
            'name' => 'Attachment storage work',
            'event_type' => 'Catering order',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ]);

        $path = 'jobs/'.$event->id.'/attachments/storage-probe.txt';
        Storage::disk('private')->put($path, 'preserve this attachment');

        $attachment = Model::withoutEvents(fn () => EventAttachment::create([
            'event_id' => $event->id,
            'business_id' => $business->id,
            'uploaded_by' => $user->id,
            'original_name' => 'storage-probe.txt',
            'disk' => 'private',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 24,
            'source' => 'upload',
        ]));

        return [$business, $user, $event, $attachment, $path];
    }
}

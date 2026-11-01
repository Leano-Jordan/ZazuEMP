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
use Tests\TestCase;

class WorkAttachmentDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }

    private function userFor(Business $business): User
    {
        $user = User::factory()->create();
        $business->users()->attach($user, ['role' => 'owner']);

        return $user;
    }

    private function eventFor(Business $business): Event
    {
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Attachment Test Customer',
        ]);

        return Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-ATT-' . Str::upper(Str::random(8)),
            'name' => 'Attachment Test Work',
            'event_type' => 'Catering order',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'draft',
        ]);
    }

    public function test_own_job_attachment_can_be_downloaded_from_private_storage(): void
    {
        Storage::fake('private');

        $business = $this->business('Attachment Download Business');
        $user = $this->userFor($business);
        $event = $this->eventFor($business);
        $path = 'jobs/'.$event->id.'/attachments/brief.txt';
        Storage::disk('private')->put($path, 'Approved event brief');

        $attachment = Model::withoutEvents(fn () => EventAttachment::create([
            'event_id' => $event->id,
            'business_id' => $business->id,
            'uploaded_by' => $user->id,
            'original_name' => 'brief.txt',
            'disk' => 'private',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 20,
            'source' => 'upload',
        ]));

        $this->actingAs($user)
            ->get(route('work.show', $event))
            ->assertOk()
            ->assertSee(route('work.attachments.download', $attachment), false);

        $this->actingAs($user)
            ->get(route('work.attachments.download', $attachment))
            ->assertOk()
            ->assertDownload('brief.txt');
    }

    public function test_foreign_job_attachment_cannot_be_downloaded(): void
    {
        Storage::fake('private');

        $first = $this->business('Attachment Own Business');
        $second = $this->business('Attachment Foreign Business');
        $user = $this->userFor($first);
        $foreignEvent = $this->eventFor($second);

        $path = 'jobs/'.$foreignEvent->id.'/attachments/foreign-secret.txt';
        Storage::disk('private')->put($path, 'foreign business confidential');

        $attachment = Model::withoutEvents(fn () => EventAttachment::create([
            'event_id' => $foreignEvent->id,
            'business_id' => $second->id,
            'uploaded_by' => null,
            'original_name' => 'foreign-secret.txt',
            'disk' => 'private',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 28,
            'source' => 'upload',
        ]));

        $this->actingAs($user)
            ->get(route('work.attachments.download', $attachment))
            ->assertNotFound();
    }
}

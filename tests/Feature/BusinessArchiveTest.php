<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteVersion;
use App\Models\User;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_archive_restores_linked_records_and_private_attachments_into_an_empty_workspace(): void
    {
        Storage::fake('private');
        $this->signInAsOwner();
        $source = auth()->user()->businesses()->firstOrFail();

        $customer = Customer::create([
            'business_id' => $source->id,
            'name' => 'Archive Customer',
            'legal_name' => 'Archive Customer Pty Ltd',
        ]);
        $contact = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Archive Contact',
            'phone' => '0123456789',
            'is_primary' => true,
        ]);
        $event = Event::create([
            'business_id' => $source->id,
            'customer_id' => $customer->id,
            'event_day_contact_id' => $contact->id,
            'reference' => 'ARCHIVE-EVENT-001',
            'name' => 'Archive Test Event',
            'customer_name' => $customer->name,
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'confirmed',
        ]);
        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'ARCHIVE-QUOTE-001',
            'status' => 'accepted',
            'currency' => 'ZAR',
        ]);
        $version = QuoteVersion::create([
            'quote_id' => $quote->id,
            'version' => 1,
            'status' => 'accepted',
            'subtotal' => 1000,
            'tax_total' => 150,
            'total' => 1150,
            'deposit_percent' => 30,
            'deposit_amount' => 345,
        ]);
        QuoteItem::create([
            'quote_version_id' => $version->id,
            'description' => 'Catering service',
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
        ]);

        $path = 'jobs/'.$event->id.'/attachments/archive-proof.txt';
        Storage::disk('private')->put($path, 'private archive attachment bytes');
        EventAttachment::create([
            'event_id' => $event->id,
            'business_id' => $source->id,
            'uploaded_by' => auth()->id(),
            'original_name' => 'archive-proof.txt',
            'disk' => 'private',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 31,
            'source' => 'upload',
        ]);

        $foreign = Business::create([
            'name' => 'Foreign Archive Tenant',
            'slug' => 'foreign-archive-tenant',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        Customer::create(['business_id' => $foreign->id, 'name' => 'Must Not Export']);

        $service = app(BusinessArchiveService::class);
        $archive = $service->download($source);

        $target = Business::create([
            'name' => 'Restore Target',
            'slug' => 'restore-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        try {
            $result = $service->restore($archive, $target);
        } finally {
            @unlink($archive);
        }

        $this->assertSame(28, $result['tables']);
        $this->assertSame(7, $result['records']);
        $this->assertSame(1, $result['files']);

        $restoredCustomer = Customer::query()->where('business_id', $target->id)->firstOrFail();
        $restoredEvent = Event::query()->where('business_id', $target->id)->firstOrFail();
        $restoredContact = CustomerContact::query()->where('customer_id', $restoredCustomer->id)->firstOrFail();
        $restoredQuote = Quote::query()->where('event_id', $restoredEvent->id)->firstOrFail();
        $restoredVersion = QuoteVersion::query()->where('quote_id', $restoredQuote->id)->firstOrFail();
        $restoredItem = QuoteItem::query()->where('quote_version_id', $restoredVersion->id)->firstOrFail();
        $attachment = EventAttachment::query()->where('business_id', $target->id)->firstOrFail();

        $this->assertSame('Archive Customer', $restoredCustomer->name);
        $this->assertSame($restoredCustomer->id, $restoredEvent->customer_id);
        $this->assertSame($restoredContact->id, $restoredEvent->event_day_contact_id);
        $this->assertSame($restoredEvent->id, $restoredQuote->event_id);
        $this->assertSame($restoredVersion->id, $restoredItem->quote_version_id);
        $this->assertSame('Catering service', $restoredItem->description);
        $this->assertNotSame($event->id, $restoredEvent->id);
        $this->assertSame($target->id, $restoredEvent->business_id);
        $this->assertSame('Restore Target', $target->fresh()->name === 'Restore Target' ? $target->name : $target->fresh()->name);
        $this->assertSame('Archive Customer', $restoredCustomer->name);
        $this->assertSame('private', $attachment->disk);
        $this->assertSame('private archive attachment bytes', Storage::disk('private')->get($attachment->path));
        $this->assertDatabaseMissing('customers', [
            'business_id' => $target->id,
            'name' => 'Must Not Export',
        ]);
        $this->assertNull($attachment->uploaded_by);
    }

    public function test_restore_refuses_a_non_empty_business_without_changing_its_records(): void
    {
        Storage::fake('private');
        $this->signInAsOwner();
        $source = auth()->user()->businesses()->firstOrFail();
        Customer::create(['business_id' => $source->id, 'name' => 'Source Customer']);

        $service = app(BusinessArchiveService::class);
        $archive = $service->download($source);
        $target = Business::create([
            'name' => 'Non Empty Target',
            'slug' => 'non-empty-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        Customer::create(['business_id' => $target->id, 'name' => 'Keep Existing']);

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('workspace is not empty');
            $service->restore($archive, $target);
        } finally {
            @unlink($archive);
        }

        $this->assertDatabaseHas('customers', [
            'business_id' => $target->id,
            'name' => 'Keep Existing',
        ]);
    }

    private function signInAsOwner(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Archive Source',
            'slug' => 'archive-source-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);
        $this->actingAs($user);
    }
}

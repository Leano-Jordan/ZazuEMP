<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
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
use Tests\TestCase;

class BusinessArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_archive_restores_linked_records_and_private_attachments_into_an_empty_workspace(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $source = Business::create([
            'name' => 'Archive Source',
            'slug' => 'archive-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($source);

        $this->seedArchiveRecords($source);

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
        $this->assertSame(8, $result['records']);
        $this->assertSame(2, $result['files']);

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
        $this->assertSame('Archive Source', $target->fresh()->name);
        $this->assertSame('Archive Customer', $restoredCustomer->name);
        $this->assertSame('private', $attachment->disk);
        $this->assertSame('private archive attachment bytes', Storage::disk('private')->get($attachment->path));
        $restoredCapability = BusinessCapability::query()->where('business_id', $target->id)->firstOrFail();
        $this->assertTrue(Storage::disk('public')->exists($restoredCapability->image_path));
        $this->assertSame('public catalogue image bytes', Storage::disk('public')->get($restoredCapability->image_path));
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
            try {
                $service->restore($archive, $target);
                $this->fail('A non-empty workspace must never accept an archive restore.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('workspace is not empty', $exception->getMessage());
            }
        } finally {
            @unlink($archive);
        }

        $this->assertDatabaseHas('customers', [
            'business_id' => $target->id,
            'name' => 'Keep Existing',
        ]);
        $this->assertDatabaseCount('customers', 2);
    }




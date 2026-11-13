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
use Illuminate\Support\Facades\DB;
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
        $sourceEvent = Event::query()->where('business_id', $source->id)->firstOrFail();
        $sourceAttachment = EventAttachment::query()->where('business_id', $source->id)->firstOrFail();
        $this->assertSame($sourceEvent->id, $sourceAttachment->event_id);

        $foreign = Business::create([
            'name' => 'Foreign Archive Tenant',
            'slug' => 'foreign-archive-tenant',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        DB::table('customers')->insert([
            'business_id' => $foreign->id,
            'name' => 'Must Not Export',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(BusinessArchiveService::class);
        $archive = $service->download($source);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($archive) === true);
        $archiveData = json_decode((string) $zip->getFromName('data.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($source->id, (int) $archiveData['events'][0]['business_id']);
        $this->assertContains($sourceEvent->id, array_map('intval', array_column($archiveData['events'], 'id')));
        $zip->close();

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
        $this->assertNotSame($sourceEvent->id, $restoredEvent->id);
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
        DB::table('customers')->insert([
            'business_id' => $target->id,
            'name' => 'Keep Existing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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


    public function test_restore_refuses_a_workspace_with_an_already_paired_device(): void
    {
        Storage::fake('private');
        $this->signInAsOwner();
        $source = auth()->user()->businesses()->firstOrFail();
        Customer::create(['business_id' => $source->id, 'name' => 'Archive Source Customer']);
        $archive = app(BusinessArchiveService::class)->download($source);

        $target = Business::create([
            'name' => 'Already Paired Target',
            'slug' => 'already-paired-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        DB::table('sync_devices')->insert([
            'business_id' => $target->id,
            'installation_id' => (string) \Illuminate\Support\Str::uuid(),
            'device_name' => 'Existing phone',
            'device_type' => 'mobile',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            try {
                app(BusinessArchiveService::class)->restore($archive, $target);
                $this->fail('A paired target must not accept a business archive restore.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('offline sync state', $exception->getMessage());
            }
        } finally {
            @unlink($archive);
        }

        $this->assertDatabaseHas('sync_devices', [
            'business_id' => $target->id,
            'device_name' => 'Existing phone',
        ]);
        $this->assertDatabaseMissing('customers', [
            'business_id' => $target->id,
            'name' => 'Archive Source Customer',
        ]);
    }

    private function seedArchiveRecords(Business $source): void
    {
        $customer = Customer::create([
            'business_id' => $source->id,
            'name' => 'Archive Customer',
            'legal_name' => 'Archive Customer Pty Ltd',
        ]);

        $capabilityImage = 'catalogue/archive-service.png';
        Storage::disk('public')->put($capabilityImage, 'public catalogue image bytes');
        BusinessCapability::create([
            'business_id' => $source->id,
            'name' => 'Catering service',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'default_price' => 1000,
            'currency' => 'ZAR',
            'image_path' => $capabilityImage,
            'is_active' => true,
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
    }

}

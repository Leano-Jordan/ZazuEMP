<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Models\SyncDevice;
use App\Support\Offline\SyncBootstrapPayloadBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineBootstrapRelationshipIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_work_quote_and_preparation_do_not_embed_foreign_customer_data(): void
    {
        $business = $this->business('Bootstrap relationship owner');
        $foreignBusiness = $this->business('Foreign customer owner');
        $foreignCustomer = Customer::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'FOREIGN CUSTOMER SECRET',
        ]);
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $foreignCustomer->id,
            'reference' => 'BOOTSTRAP-REL-001',
            'name' => 'Local event with corrupted customer link',
            'event_type' => 'Other',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);
        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'BOOTSTRAP-QUOTE-001',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);
        $preparation = EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'title' => 'Prepare venue',
            'status' => 'pending',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'metadata' => [
                'bootstrap_selection_mode' => 'explicit',
                'bootstrap_selection' => [
                    ['type' => 'job', 'id' => $event->id],
                    ['type' => 'quote', 'id' => $quote->id],
                    ['type' => 'preparation', 'id' => $preparation->id],
                ],
            ],
        ]);

        $payload = app(SyncBootstrapPayloadBuilder::class)->build($device);
        $selected = collect($payload['selected'])->keyBy('type');

        $jobRecord = $selected['job']['record'];
        $quoteRecord = $selected['quote']['record'];
        $preparationRecord = $selected['preparation']['record'];

        $this->assertNull($jobRecord['customer']);
        $this->assertNull($jobRecord['customer_local_id']);
        $this->assertNull($quoteRecord['event']['customer']);
        $this->assertNull($preparationRecord['event']['customer']);
        $this->assertStringNotContainsString('FOREIGN CUSTOMER SECRET', json_encode($selected));
    }

    public function test_invoice_bootstrap_omits_a_quote_version_owned_by_another_business(): void
    {
        $business = $this->business('Invoice bootstrap owner');
        $foreignBusiness = $this->business('Foreign quote version owner');
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'INVOICE-BOOTSTRAP-001',
            'name' => 'Local invoice event',
            'event_type' => 'Other',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);
        $foreignCustomer = Customer::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign quote customer',
        ]);
        $foreignEvent = Event::create([
            'business_id' => $foreignBusiness->id,
            'customer_id' => $foreignCustomer->id,
            'reference' => 'FOREIGN-QUOTE-EVENT-001',
            'name' => 'Foreign quote event',
            'event_type' => 'Other',
            'event_date' => now()->addDays(12)->toDateString(),
            'status' => 'confirmed',
        ]);
        $foreignQuote = Quote::create([
            'event_id' => $foreignEvent->id,
            'reference' => 'FOREIGN-QUOTE-001',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);
        $foreignVersion = QuoteVersion::create([
            'quote_id' => $foreignQuote->id,
            'version' => 1,
            'status' => 'sent',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'deposit_percent' => '50.00',
            'deposit_amount' => '50.00',
        ]);
        $invoice = Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'quote_id' => $foreignQuote->id,
            'quote_version_id' => $foreignVersion->id,
            'number' => 'INV-BOOTSTRAP-001',
            'status' => 'draft',
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);

        $payload = app(SyncBootstrapPayloadBuilder::class)->build($device);
        $record = collect($payload['invoices'])->firstWhere('server_id', $invoice->id)['record'];

        $this->assertNull($record['quote_id']);
        $this->assertNull($record['quote_version_id']);
        $this->assertNull($record['quote_version']);
        $this->assertSame('0.00', $record['deposit_required']);
        $this->assertStringNotContainsString('Foreign quote event', json_encode($record));
    }

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }
}

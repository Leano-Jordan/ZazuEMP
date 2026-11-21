<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\Quote;
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

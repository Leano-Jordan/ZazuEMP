<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\QuoteVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParentChildIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(7)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }

    private function eventFor(Business $business, string $reference): Event
    {
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => $business->name . ' Customer',
        ]);

        return Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => $reference,
            'name' => 'Integrity Event',
            'event_type' => 'Wedding',
            'event_date' => now()->addDays(7)->toDateString(),
            'status' => 'draft',
        ]);
    }

    public function test_quote_from_another_business_cannot_be_opened_in_the_current_workspace(): void
    {
        $own = $this->business('Own Business');
        $foreign = $this->business('Foreign Business');
        $user = $this->signInAsOwner($own);

        $event = $this->eventFor($foreign, 'ZAZ-FOREIGN-QUOTE');

        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-FOREIGN-001',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);

        $this->actingAs($user)
            ->get(route('quotes.show', $quote))
            ->assertNotFound();
    }

    public function test_quote_version_must_belong_to_the_quote_being_edited(): void
    {
        $own = $this->business('Own Business');
        $foreign = $this->business('Foreign Business');
        $user = $this->signInAsOwner($own);

        $ownEvent = $this->eventFor($own, 'ZAZ-OWN-VERSION');
        $foreignEvent = $this->eventFor($foreign, 'ZAZ-FOREIGN-VERSION');

        $ownQuote = Quote::create([
            'event_id' => $ownEvent->id,
            'reference' => 'QUO-OWN-001',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);

        $foreignQuote = Quote::create([
            'event_id' => $foreignEvent->id,
            'reference' => 'QUO-FOREIGN-002',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);

        $foreignVersion = $foreignQuote->versions()->create([
            'version' => 1,
            'status' => 'draft',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
        ]);

        $this->actingAs($user)
            ->get(route('quotes.versions.edit', [$ownQuote, $foreignVersion]))
            ->assertNotFound();
    }

    public function test_invoice_creation_rejects_a_quote_from_another_business_when_an_own_event_is_supplied(): void
    {
        $own = $this->business('Own Business');
        $foreign = $this->business('Foreign Business');
        $user = $this->signInAsOwner($own);

        $ownEvent = $this->eventFor($own, 'ZAZ-OWN-INVOICE');
        $foreignEvent = $this->eventFor($foreign, 'ZAZ-FOREIGN-INVOICE');

        $foreignQuote = Quote::create([
            'event_id' => $foreignEvent->id,
            'reference' => 'QUO-FOREIGN-003',
            'status' => 'accepted',
            'currency' => 'ZAR',
        ]);

        $foreignVersion = $foreignQuote->versions()->create([
            'version' => 1,
            'status' => 'accepted',
            'subtotal' => '500.00',
            'tax_total' => '0.00',
            'total' => '500.00',
        ]);

        $foreignVersion->items()->create([
            'description' => 'Foreign service',
            'quantity' => '1.00',
            'unit' => 'event',
            'unit_price' => '500.00',
            'line_total' => '500.00',
            'source_snapshot' => ['description' => 'Foreign service'],
        ]);

        $this->actingAs($user)
            ->post(route('finance.invoices.store'), [
                'quote_id' => $foreignQuote->id,
                'event_id' => $ownEvent->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_payment_cannot_post_against_a_foreign_business_invoice(): void
    {
        $own = $this->business('Own Business');
        $foreign = $this->business('Foreign Business');
        $user = $this->signInAsOwner($own);

        $foreignInvoice = Invoice::create([
            'business_id' => $foreign->id,
            'number' => 'INV-FOREIGN-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '500.00',
            'tax_total' => '0.00',
            'total' => '500.00',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('finance.payments.store'), [
                'invoice_id' => $foreignInvoice->id,
                'amount' => '100.00',
                'method' => 'bank_transfer',
                'paid_at' => now()->toDateString(),
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_inventory_movement_cannot_attach_an_event_from_another_business(): void
    {
        $own = $this->business('Own Business');
        $foreign = $this->business('Foreign Business');
        $user = $this->signInAsOwner($own);

        $item = InventoryItem::create([
            'business_id' => $own->id,
            'name' => 'Plates',
            'unit' => 'plate',
            'reorder_level' => 0,
        ]);

        $foreignEvent = $this->eventFor($foreign, 'ZAZ-FOREIGN-STOCK');

        $this->actingAs($user)
            ->post(route('inventory.movement', $item), [
                'type' => 'receipt',
                'quantity' => '10.00',
                'unit_cost' => '10.00',
                'movement_date' => now()->toDateString(),
                'event_id' => $foreignEvent->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('inventory_movements', 0);
    }
}

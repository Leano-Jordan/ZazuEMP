<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteCommercialHandoffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    public function test_customer_can_view_and_accept_a_sent_quote_through_a_signed_link(): void
    {
        $customer = Customer::create(['name' => 'Public Quote Customer']);

        $event = Event::create([
            'business_id' => app(\App\Support\CurrentBusiness::class)->id(auth()->user()),
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-PUBLIC-001',
            'name' => 'Public Acceptance Event',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);

        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-PUBLIC-001',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);

        $version = $quote->versions()->create([
            'version' => 1,
            'status' => 'sent',
            'subtotal' => '1000.00',
            'tax_total' => '150.00',
            'total' => '1150.00',
        ]);

        $version->items()->create([
            'description' => 'Public catering package',
            'quantity' => '1.00',
            'unit' => 'event',
            'unit_price' => '1000.00',
            'line_total' => '1000.00',
            'source_snapshot' => ['description' => 'Public catering package'],
        ]);

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'quotes.public',
            now()->addHour(),
            ['quote' => $quote]
        );

        $this->get($url)
            ->assertOk()
            ->assertSee('QUO-PUBLIC-001')
            ->assertSee('Public Quote Customer')
            ->assertSee('Accept quote');

        $acceptUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'quotes.public.accept',
            now()->addHour(),
            ['quote' => $quote]
        );

        $this->post($acceptUrl, [
            'customer_name' => 'Public Quote Customer',
            'acceptance' => '1',
        ])->assertRedirect();

        $this->assertSame('accepted', $quote->fresh()->status);
        $this->assertSame('accepted', $version->fresh()->status);
        $this->assertSame('confirmed', $event->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $event->business_id,
            'action' => 'quote.customer.accepted',
            'subject_id' => $quote->id,
        ]);

        $this->get(
            \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'quotes.public',
                now()->addHour(),
                ['quote' => $quote]
            )
        )
            ->assertOk()
            ->assertSee('Quote accepted');
    }

    public function test_staff_accepting_a_quote_confirms_a_draft_job(): void
    {
        $customer = Customer::create(['name' => 'Staff Acceptance Customer']);
        $event = Event::create([
            'business_id' => app(\App\Support\CurrentBusiness::class)->id(auth()->user()),
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-STAFF-ACCEPT-001',
            'name' => 'Staff Acceptance Event',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'draft',
        ]);

        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-STAFF-001',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);
        $quote->versions()->create([
            'version' => 1,
            'status' => 'sent',
            'subtotal' => '1000.00',
            'tax_total' => '0.00',
            'total' => '1000.00',
        ]);

        $this->patch(route('quotes.status', $quote), ['status' => 'accepted'])
            ->assertRedirect(route('quotes.show', $quote));

        $this->assertSame('accepted', $quote->fresh()->status);
        $this->assertSame('confirmed', $event->fresh()->status);
    }

    public function test_accepted_quote_exposes_invoice_hand_off_and_preserves_existing_invoice_link(): void
    {
        $customer = Customer::create(['name' => 'Finance Handoff Customer']);

        $event = Event::create([
            'business_id' => app(\App\Support\CurrentBusiness::class)->id(auth()->user()),
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-FINANCE-HANDOFF-001',
            'name' => 'Finance Handoff Event',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);

        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-FINANCE-HANDOFF-001',
            'status' => 'accepted',
            'currency' => 'ZAR',
        ]);

        $version = $quote->versions()->create([
            'version' => 1,
            'status' => 'accepted',
            'subtotal' => '1000.00',
            'tax_total' => '150.00',
            'total' => '1150.00',
        ]);

        $response = $this->get(route('quotes.show', $quote));

        $response->assertOk()
            ->assertSee('Accepted quote → invoice')
            ->assertSee(route('finance.invoices.create', ['quote_id' => $quote->id]), false);

        $invoice = \App\Models\Invoice::create([
            'business_id' => $event->business_id,
            'event_id' => $event->id,
            'quote_id' => $quote->id,
            'quote_version_id' => $version->id,
            'number' => 'INV-FINANCE-HANDOFF-001',
            'idempotency_key' => (string) \Illuminate\Support\Str::uuid(),
            'business_legal_name' => 'Test Business',
            'customer_name' => $customer->name,
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '1000.00',
            'tax_total' => '150.00',
            'total' => '1150.00',
        ]);

        $response = $this->get(route('quotes.show', $quote));

        $response->assertOk()
            ->assertSee('Open invoice INV-FINANCE-HANDOFF-001')
            ->assertSee(route('finance.invoices.show', $invoice), false);
    }
}

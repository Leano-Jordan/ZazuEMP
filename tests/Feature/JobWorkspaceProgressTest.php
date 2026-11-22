<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\EventRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobWorkspaceProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    public function test_job_workspace_shows_customer_waiting_state_after_quote_is_sent(): void
    {
        $customer = Customer::create(['name' => 'Sent Quote Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZU-NEXT-SENT-001',
            'name' => 'Sent Quote Job',
            'event_date' => '2026-12-12',
            'status' => 'confirmed',
        ]);

        EventRequirement::create([
            'event_id' => $event->id,
            'description' => 'Catering service',
            'category' => 'Catering',
            'quantity' => 1,
            'unit' => 'service',
            'status' => 'open',
        ]);
        $event->quotes()->create([
            'reference' => 'QUO-NEXT-SENT-001',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);

        $response = $this->get(route('work.show', $event));

        $response->assertOk()
            ->assertSee('Check customer decision')
            ->assertSee('Waiting for customer')
            ->assertSee('Invoice & payment', false)
            ->assertSee('Delivery readiness')
            ->assertSee('Close-out')
            ->assertSee('The quote has been sent. Check for the customer’s response before moving forward.');
    }

    public function test_job_workspace_shows_invoice_needed_after_quote_acceptance(): void
    {
        $customer = Customer::create(['name' => 'Accepted Quote Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZU-NEXT-ACCEPTED-001',
            'name' => 'Accepted Quote Job',
            'event_date' => '2026-12-13',
            'status' => 'confirmed',
        ]);

        EventRequirement::create([
            'event_id' => $event->id,
            'description' => 'Catering service',
            'category' => 'Catering',
            'quantity' => 1,
            'unit' => 'service',
            'status' => 'open',
        ]);
        $quote = $event->quotes()->create([
            'reference' => 'QUO-NEXT-ACCEPTED-001',
            'status' => 'accepted',
            'currency' => 'ZAR',
        ]);

        $response = $this->get(route('work.show', $event));

        $response->assertOk()
            ->assertSee('Create invoice')
            ->assertSee('Accepted')
            ->assertSee('Not invoiced')
            ->assertSee(route('finance.invoices.create', ['quote_id' => $quote->id]), false)
            ->assertSee('The customer accepted the quote. Create the invoice from this accepted quote to begin tracking payment.');
    }

    public function test_job_workspace_marks_missing_scope_as_the_current_progress_stage(): void
    {
        $customer = Customer::create(['name' => 'Scope Pending Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZU-NEXT-SCOPE-001',
            'name' => 'Scope Pending Job',
            'event_date' => '2026-12-14',
            'status' => 'draft',
        ]);

        $response = $this->get(route('work.show', $event));

        $response->assertOk()
            ->assertSee('No services yet')
            ->assertSee('Not created')
            ->assertSee('Add services');
        $this->assertStringContainsString('aria-current="step"', $response->getContent());
    }

    public function test_job_workspace_keeps_older_outstanding_invoice_visible_when_a_newer_invoice_is_paid(): void
    {
        $customer = Customer::create(['name' => 'Multiple Invoice Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZU-NEXT-MULTI-001',
            'name' => 'Multiple Invoice Job',
            'event_date' => '2026-12-15',
            'status' => 'confirmed',
        ]);
        $event->quotes()->create([
            'reference' => 'QUO-NEXT-MULTI-001',
            'status' => 'accepted',
            'currency' => 'ZAR',
        ]);

        $olderOutstanding = Invoice::create([
            'business_id' => $event->business_id,
            'event_id' => $event->id,
            'number' => 'INV-NEXT-OLDER-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
            'issued_at' => now()->subDays(2)->toDateString(),
        ]);
        $newerPaid = Invoice::create([
            'business_id' => $event->business_id,
            'event_id' => $event->id,
            'number' => 'INV-NEXT-NEWER-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '200.00',
            'tax_total' => '0.00',
            'total' => '200.00',
            'issued_at' => now()->toDateString(),
        ]);
        Payment::create([
            'business_id' => $event->business_id,
            'invoice_id' => $newerPaid->id,
            'event_id' => $event->id,
            'type' => 'payment',
            'idempotency_key' => 'job-progress-paid-newer-invoice',
            'amount' => '200.00',
            'currency' => 'ZAR',
            'method' => 'cash',
            'paid_at' => now()->toDateString(),
        ]);

        $this->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('1 invoice due')
            ->assertSee('Record payment')
            ->assertSee(route('finance.payments.create', ['invoice_id' => $olderOutstanding->id]), false);
    }
}

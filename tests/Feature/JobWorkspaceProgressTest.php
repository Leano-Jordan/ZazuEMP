<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
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
            ->assertSee('Invoice &amp; payment')
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
}

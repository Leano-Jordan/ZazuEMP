<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_v1_is_created_from_current_requirements_and_totals_are_persisted(): void
    {
        $customer = Customer::create([
            'name' => 'Quote Customer',
        ]);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-001',
            'name' => 'Quote Test Event',
            'event_date' => '2026-10-10',
            'status' => 'draft',
        ]);

        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '100 white chairs',
            'category' => 'Furniture',
            'quantity' => 100,
            'unit' => 'chairs',
            'notes' => 'Folding chairs',
            'status' => 'open',
        ]);

        $response = $this->post(route('work.quotes.store', $event), [
            'currency' => 'zar',
            'notes' => 'Initial draft',
            'unit_price' => [
                $requirement->id => 25.50,
            ],
        ]);

        $quote = Quote::query()->firstOrFail();
        $version = $quote->versions()->firstOrFail();
        $item = $version->items()->firstOrFail();

        $response->assertRedirect(route('quotes.show', $quote));
        $response->assertSessionHas('success', 'Draft quote created.');

        $this->assertSame('ZAR', $quote->currency);
        $this->assertSame(1, $version->version);
        $this->assertSame('2550.00', (string) $version->subtotal);
        $this->assertSame('2550.00', (string) $version->total);
        $this->assertSame('100.00', (string) $item->quantity);
        $this->assertSame('25.50', (string) $item->unit_price);
        $this->assertSame('2550.00', (string) $item->line_total);
        $this->assertSame('100 white chairs', $item->source_snapshot['description']);
    }

    public function test_new_quote_revision_supersedes_the_previous_revision_without_changing_its_snapshot(): void
    {
        $customer = Customer::create([
            'name' => 'Revision Customer',
        ]);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-002',
            'name' => 'Revision Test Event',
            'event_date' => '2026-10-11',
            'status' => 'draft',
        ]);

        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '50 chairs',
            'quantity' => 50,
            'unit' => 'chairs',
            'status' => 'open',
        ]);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'unit_price' => [
                $requirement->id => 40,
            ],
        ]);

        $quote = Quote::query()->firstOrFail();
        $original = $quote->versions()->with('items')->firstOrFail();

        $requirement->update([
            'description' => '80 chairs',
            'quantity' => 80,
        ]);

        $response = $this->post(route('quotes.versions.store', $quote));

        $quote->refresh()->load('versions.items');

        $newest = $quote->versions->sortByDesc('version')->first();

        $response->assertRedirect(route('quotes.show', $quote));
        $this->assertSame('superseded', $original->fresh()->status);
        $this->assertSame(2, $newest->version);
        $this->assertSame('50 chairs', $original->items->first()->description);
        $this->assertSame('50 chairs', $newest->items->first()->description);
        $this->assertSame('50.00', (string) $newest->items->first()->quantity);
    }

    public function test_quote_creation_requires_a_price_for_each_current_requirement(): void
    {
        $customer = Customer::create(['name' => 'Validation Customer']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-003',
            'name' => 'Validation Event',
            'event_date' => '2026-10-12',
            'status' => 'draft',
        ]);

        EventRequirement::create([
            'event_id' => $event->id,
            'description' => '10 tables',
            'quantity' => 10,
            'unit' => 'tables',
            'status' => 'open',
        ]);

        $response = $this->from(route('work.quotes.create', $event))
            ->post(route('work.quotes.store', $event), [
                'currency' => 'ZAR',
                'unit_price' => [],
            ]);

        $response->assertRedirect(route('work.quotes.create', $event));
        $response->assertSessionHasErrors('unit_price');
        $this->assertDatabaseCount('quotes', 0);
    }
}

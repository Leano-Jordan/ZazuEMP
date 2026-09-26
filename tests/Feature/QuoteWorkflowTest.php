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
    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

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

    public function test_quote_revision_rebuilds_from_current_requirements_and_preserves_previous_snapshot_until_saved(): void
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

        $createResponse = $this->post(route('quotes.versions.store', $quote));

        $quote->refresh()->load('versions.items');
        $newest = $quote->versions->sortByDesc('version')->first();

        $createResponse->assertRedirect(route('quotes.versions.edit', [$quote, $newest]));
        $this->assertSame('draft', $original->fresh()->status);
        $this->assertSame(2, $newest->version);
        $this->assertSame('50 chairs', $original->items->first()->description);
        $this->assertSame('80 chairs', $newest->items->first()->description);
        $this->assertSame('80.00', (string) $newest->items->first()->quantity);
        $this->assertSame('40.00', (string) $newest->items->first()->unit_price);
    }

    public function test_quote_draft_revision_can_be_saved_with_updated_prices(): void
    {
        $customer = Customer::create(['name' => 'Revision Save Customer']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-006',
            'name' => 'Revision Save Event',
            'event_date' => '2026-10-15',
            'status' => 'draft',
        ]);

        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '80 chairs',
            'quantity' => 80,
            'unit' => 'chairs',
            'status' => 'open',
        ]);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'unit_price' => [$requirement->id => 40],
        ]);

        $quote = Quote::query()->firstOrFail();
        $original = $quote->versions()->firstOrFail();

        $requirement->update(['quantity' => 100]);
        $this->post(route('quotes.versions.store', $quote));

        $version = $quote->refresh()->versions()->where('version', 2)->firstOrFail();

        $response = $this->put(route('quotes.versions.update', [$quote, $version]), [
            'notes' => 'Updated revision',
            'unit_price' => [$requirement->id => 50],
        ]);

        $version->refresh()->load('items');
        $original->refresh();

        $response->assertRedirect(route('quotes.show', $quote));
        $this->assertSame('superseded', $original->status);
        $this->assertSame('draft', $version->status);
        $this->assertSame('100.00', (string) $version->items->first()->quantity);
        $this->assertSame('50.00', (string) $version->items->first()->unit_price);
        $this->assertSame('5000.00', (string) $version->items->first()->line_total);
        $this->assertSame('Updated revision', $version->notes);
    }

    public function test_quote_creation_rejects_prices_with_more_than_two_decimal_places(): void
    {
        $customer = Customer::create(['name' => 'Precision Customer']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-004',
            'name' => 'Precision Event',
            'event_date' => '2026-10-13',
            'status' => 'draft',
        ]);

        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '10 plates',
            'quantity' => 10,
            'unit' => 'plates',
            'status' => 'open',
        ]);

        $response = $this->from(route('work.quotes.create', $event))
            ->post(route('work.quotes.store', $event), [
                'currency' => 'ZAR',
                'unit_price' => [$requirement->id => '12.345'],
            ]);

        $response->assertRedirect(route('work.quotes.create', $event));
        $response->assertSessionHasErrors('unit_price.' . $requirement->id);
        $this->assertDatabaseCount('quotes', 0);
    }

    public function test_work_workspace_detects_when_a_quote_is_stale_against_changed_requirements(): void
    {
        $customer = Customer::create(['name' => 'Stale Quote Customer']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-005',
            'name' => 'Stale Quote Event',
            'event_date' => '2026-10-14',
            'status' => 'draft',
        ]);

        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '25 chairs',
            'quantity' => 25,
            'unit' => 'chairs',
            'status' => 'open',
        ]);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'unit_price' => [$requirement->id => 40],
        ]);

        $requirement->update(['quantity' => 30]);

        $response = $this->get(route('work.show', $event));

        $response->assertOk();
        $response->assertSee('Quote needs review');
        $response->assertSee('Revise quote');
        $response->assertSee(route('quotes.versions.store', $event->quotes()->first()), false);
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

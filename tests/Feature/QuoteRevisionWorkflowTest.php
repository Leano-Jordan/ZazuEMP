<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRevisionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
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
            'quantity' => [$requirement->id => '50.00'],
            'unit_price' => [
                $requirement->id => 40,
            ],
        ])->assertRedirect();

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
        $this->assertSame('superseded', $original->fresh()->status);
        $this->assertSame(2, $newest->version);
        $this->assertSame('50 chairs', $original->items->first()->description);
        $this->assertSame('80 chairs', $newest->items->first()->description);
        $this->assertSame('80.00', (string) $newest->items->first()->quantity);
        $this->assertSame('40.00', (string) $newest->items->first()->unit_price);
        $this->assertSame('80 chairs', $newest->items->first()->source_snapshot['description']);
        $this->assertSame('80.00', $newest->items->first()->source_snapshot['quantity']);
    }

    public function test_quote_revision_includes_new_requirements_and_recalculates_totals(): void
    {
        $customer = Customer::create(['name' => 'New Requirement Customer']);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-007',
            'name' => 'New Requirement Event',
            'event_date' => '2026-10-16',
            'status' => 'draft',
        ]);

        $first = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '10 tables',
            'quantity' => 10,
            'unit' => 'tables',
            'status' => 'open',
        ]);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'quantity' => [$first->id => '10.00'],
            'unit_price' => [$first->id => '100.00'],
        ]);

        $quote = Quote::query()->firstOrFail();

        $second = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '20 chairs',
            'quantity' => 20,
            'unit' => 'chairs',
            'status' => 'open',
        ]);

        $this->post(route('quotes.versions.store', $quote))
            ->assertRedirect();

        $version = $quote->refresh()->versions()->where('version', 2)->firstOrFail();
        $version->load('items');

        $this->assertCount(2, $version->items);
        $this->assertSame('1000.00', (string) $version->subtotal);
        $this->assertSame('1000.00', (string) $version->total);
        $this->assertSame('1000.00', (string) $version->items->firstWhere('event_requirement_id', $first->id)->line_total);
        $this->assertSame('0.00', (string) $version->items->firstWhere('event_requirement_id', $second->id)->line_total);
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
            'quantity' => [$requirement->id => '80.00'],
            'unit_price' => [$requirement->id => 40],
        ]);

        $quote = Quote::query()->firstOrFail();
        $original = $quote->versions()->firstOrFail();

        $requirement->update(['quantity' => 100]);
        $this->post(route('quotes.versions.store', $quote));

        $version = $quote->refresh()->versions()->where('version', 2)->firstOrFail();

        $response = $this->put(route('quotes.versions.update', [$quote, $version]), [
            'notes' => 'Updated revision',
            'quantity' => [$requirement->id => '100.00'],
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
}

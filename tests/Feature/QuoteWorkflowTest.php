<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Models\Quote;
use App\Services\QuoteService;
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
            'quantity' => [$requirement->id => '100.00'],
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

    public function test_quote_quantity_can_be_confirmed_and_corrected_on_initial_quote_and_revision(): void
    {
        $customer = Customer::create(['name' => 'Quantity Review Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-QTY-001',
            'name' => 'Quantity Review Event',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'draft',
        ]);
        $requirement = $event->requirements()->create([
            'description' => 'Catering package',
            'category' => 'service',
            'quantity' => '1.00',
            'unit' => 'guest',
            'status' => 'open',
        ]);

        $this->get(route('work.quotes.create', $event))
            ->assertOk()
            ->assertSee('name="quantity[' . $requirement->id . ']"', false)
            ->assertSee('data-quote-line-total', false);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'quantity' => [$requirement->id => '120.00'],
            'unit_price' => [$requirement->id => '85.00'],
        ])->assertRedirect();

        $quote = Quote::query()->firstOrFail();
        $firstVersion = $quote->latestVersion()->with('items')->firstOrFail();
        $firstItem = $firstVersion->items->firstOrFail();

        $this->assertSame('120.00', (string) $requirement->fresh()->quantity);
        $this->assertSame('120.00', (string) $firstItem->quantity);
        $this->assertSame('10200.00', (string) $firstVersion->total);

        $requirement->update(['quantity' => '125.00']);
        $this->post(route('quotes.versions.store', $quote))->assertRedirect();
        $revision = $quote->refresh()->latestVersion()->firstOrFail();

        $this->put(route('quotes.versions.update', [$quote, $revision]), [
            'quantity' => [$requirement->id => '150.00'],
            'unit_price' => [$requirement->id => '90.00'],
            'deposit_percent' => '0.00',
        ])->assertRedirect(route('quotes.show', $quote));

        $revision->refresh()->load('items');
        $this->assertSame('150.00', (string) $requirement->fresh()->quantity);
        $this->assertSame('150.00', (string) $revision->items->first()->quantity);
        $this->assertSame('13500.00', (string) $revision->total);
        $this->assertSame('120.00', (string) $firstVersion->fresh()->items()->firstOrFail()->quantity);
    }

    public function test_quote_deposit_percent_calculates_from_final_total(): void
    {
        $customer = \App\Models\Customer::create([
            'name' => 'Deposit Quote Customer',
        ]);

        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'DEP-Q-001',
            'name' => 'Deposit Quote Event',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);

        $requirement = $event->requirements()->create([
            'description' => 'Catering',
            'category' => 'service',
            'quantity' => '1.00',
            'unit' => 'event',
        ]);

        $quote = app(\App\Services\QuoteService::class)->createFromRequirements(
            $event,
            collect([$requirement->load('capability')]),
            [$requirement->id => '1000.00'],
            'ZAR',
            null,
            null,
            '20.00'
        );

        $version = $quote->latestVersion;

        $this->assertSame('20.00', $version->deposit_percent);
        $this->assertSame('200.00', $version->deposit_amount);
    }

    public function test_customer_quote_link_rejects_an_invalid_signature(): void
    {
        $customer = Customer::create(['name' => 'Invalid Link Customer']);
        $event = Event::create([
            'business_id' => app(\App\Support\CurrentBusiness::class)->id(auth()->user()),
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-PUBLIC-002',
            'name' => 'Invalid Link Event',
            'event_date' => now()->addDays(10)->toDateString(),
            'status' => 'confirmed',
        ]);

        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-PUBLIC-002',
            'status' => 'sent',
            'currency' => 'ZAR',
        ]);

        $this->get(route('quotes.public', $quote))
            ->assertForbidden();
    }

    public function test_signed_customer_quote_can_be_viewed_and_accepted(): void
    {
        $customer = Customer::create(['name' => 'Customer Acceptance']);
        $event = Event::create([
            'business_id' => app(\App\Support\CurrentBusiness::class)->id(auth()->user()),
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-PUBLIC-003',
            'name' => 'Customer Acceptance Event',
            'event_date' => now()->addDays(14)->toDateString(),
            'status' => 'confirmed',
        ]);

        $requirement = $event->requirements()->create([
            'description' => 'Catering service',
            'category' => 'service',
            'quantity' => '1.00',
            'unit' => 'event',
        ]);

        $quote = app(\App\Services\QuoteService::class)->createFromRequirements(
            $event,
            collect([$requirement->load('capability')]),
            [$requirement->id => '2500.00'],
            'ZAR',
            null,
            'Customer-facing acceptance test',
            '20.00'
        );

        $quote->update(['status' => 'sent']);
        $quote->latestVersion()->update(['status' => 'sent']);

        $viewUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'quotes.public',
            now()->addMinutes(10),
            ['quote' => $quote->id]
        );

        $view = $this->get($viewUrl);
        $view->assertOk();
        $view->assertSee($quote->reference);
        $view->assertSee('Customer Acceptance');
        $view->assertSee('ZAR 2,500.00');
        $view->assertSee('Accept quote');

        $acceptUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'quotes.public.accept',
            now()->addMinutes(10),
            ['quote' => $quote->id]
        );

        $this->post($acceptUrl, [
            'customer_name' => 'Customer Acceptance',
            'acceptance' => '1',
        ])->assertRedirect();

        $this->assertSame('accepted', $quote->fresh()->status);
        $this->assertSame('accepted', $quote->latestVersion()->firstOrFail()->fresh()->status);
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
                'quantity' => [$requirement->id => '10.00'],
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
            'quantity' => [$requirement->id => '25.00'],
            'unit_price' => [$requirement->id => 40],
        ]);

        $requirement->update(['quantity' => 30]);

        $response = $this->get(route('work.show', $event));

        $response->assertOk();
        $response->assertSee('Quote needs review');
        $response->assertSee('Revise quote');
        $response->assertSee(route('quotes.versions.store', $event->quotes()->first()), false);
    }

    public function test_quote_creation_rejects_missing_quantity_confirmation(): void
    {
        $customer = Customer::create(['name' => 'Quantity Required Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-QTY-002',
            'name' => 'Quantity Required Event',
            'event_date' => now()->addDays(12)->toDateString(),
            'status' => 'draft',
        ]);
        $requirement = $event->requirements()->create([
            'description' => 'Single service starter',
            'quantity' => '1.00',
            'unit' => 'service',
            'status' => 'open',
        ]);

        $this->from(route('work.quotes.create', $event))
            ->post(route('work.quotes.store', $event), [
                'currency' => 'ZAR',
                'unit_price' => [$requirement->id => '500.00'],
            ])
            ->assertRedirect(route('work.quotes.create', $event))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('quotes', 0);
        $this->assertSame('1.00', (string) $requirement->fresh()->quantity);
    }

    public function test_quote_revision_rejects_missing_quantity_confirmation(): void
    {
        $customer = Customer::create(['name' => 'Revision Quantity Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-QTY-003',
            'name' => 'Revision Quantity Event',
            'event_date' => now()->addDays(13)->toDateString(),
            'status' => 'draft',
        ]);
        $requirement = $event->requirements()->create([
            'description' => 'Starter service',
            'quantity' => '1.00',
            'unit' => 'service',
            'status' => 'open',
        ]);
        $quote = app(QuoteService::class)->createFromRequirements(
            $event,
            collect([$requirement->load('capability')]),
            [$requirement->id => '500.00'],
            'ZAR',
            null,
            null
        );

        $this->post(route('quotes.versions.store', $quote))->assertRedirect();
        $revision = $quote->refresh()->latestVersion()->firstOrFail();

        $this->from(route('quotes.versions.edit', [$quote, $revision]))
            ->put(route('quotes.versions.update', [$quote, $revision]), [
                'unit_price' => [$requirement->id => '550.00'],
            ])
            ->assertRedirect(route('quotes.versions.edit', [$quote, $revision]))
            ->assertSessionHasErrors('quantity');

        $this->assertSame('1.00', (string) $requirement->fresh()->quantity);
        $this->assertSame('500.00', (string) $revision->fresh()->items()->firstOrFail()->unit_price);
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

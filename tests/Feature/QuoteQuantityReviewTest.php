<?php

namespace Tests\Feature;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use App\Services\QuoteService;
use App\Support\CurrentBusiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteQuantityReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
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
            ->assertSee('name="quantity['.$requirement->id.']"', false)
            ->assertSee('Quantity to quote')
            ->assertSee('Every line needs your attention.')
            ->assertSee('Do not leave this at one unless one is genuinely correct.')
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

    public function test_quote_line_items_visibly_distinguish_products_from_services(): void
    {
        $businessId = app(CurrentBusiness::class)->id(auth()->user());
        $product = BusinessCapability::create([
            'business_id' => $businessId,
            'name' => 'Chicken portions',
            'category' => 'Catering',
            'capability_type' => 'product',
            'pricing_basis' => 'per_unit',
            'default_price' => '45.00',
            'currency' => 'ZAR',
            'default_unit' => 'portion',
            'is_active' => true,
        ]);
        $service = BusinessCapability::create([
            'business_id' => $businessId,
            'name' => 'Waiter service',
            'category' => 'Staffing',
            'capability_type' => 'service',
            'pricing_basis' => 'per_hour',
            'default_price' => '120.00',
            'currency' => 'ZAR',
            'default_unit' => 'hour',
            'is_active' => true,
        ]);
        $customer = Customer::create(['name' => 'Product Service Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-QUOTE-TYPES-001',
            'name' => 'Product Service Event',
            'event_date' => now()->addDays(15)->toDateString(),
            'status' => 'draft',
        ]);
        $productRequirement = $event->requirements()->create([
            'capability_id' => $product->id,
            'description' => 'Chicken portions',
            'category' => 'Catering',
            'quantity' => '1.00',
            'unit' => 'portion',
            'status' => 'open',
        ]);
        $serviceRequirement = $event->requirements()->create([
            'capability_id' => $service->id,
            'description' => 'Waiter service',
            'category' => 'Staffing',
            'quantity' => '1.00',
            'unit' => 'hour',
            'status' => 'open',
        ]);
        $starterServiceRequirement = $event->requirements()->create([
            'description' => 'Setup and cleanup',
            'category' => 'Event operations',
            'quantity' => '1.00',
            'unit' => 'service',
            'status' => 'open',
        ]);

        $this->get(route('work.quotes.create', $event))
            ->assertOk()
            ->assertSee('Product')
            ->assertSee('Service')
            ->assertSee('Chicken portions')
            ->assertSee('Waiter service')
            ->assertSee('Setup and cleanup')
            ->assertDontSee('Custom line');

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'quantity' => [
                $productRequirement->id => '100.00',
                $serviceRequirement->id => '8.00',
                $starterServiceRequirement->id => '1.00',
            ],
            'confirm_single_quantity' => [
                $starterServiceRequirement->id => '1',
            ],
            'unit_price' => [
                $productRequirement->id => '45.00',
                $serviceRequirement->id => '120.00',
                $starterServiceRequirement->id => '300.00',
            ],
        ])->assertRedirect();

        $quote = Quote::query()->firstOrFail();
        $this->post(route('quotes.versions.store', $quote))->assertRedirect();
        $revision = $quote->refresh()->latestVersion()->firstOrFail();

        $this->get(route('quotes.versions.edit', [$quote, $revision]))
            ->assertOk()
            ->assertSee('Product')
            ->assertSee('Service')
            ->assertSee('Setup and cleanup')
            ->assertDontSee('Custom line');
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
}

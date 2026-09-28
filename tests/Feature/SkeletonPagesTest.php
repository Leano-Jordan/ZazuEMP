<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkeletonPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = $this->signInAsOwner();
        $business = $user->businesses()->first();
        $user->businesses()->updateExistingPivot($business->id, ['experience_level' => 'intermediate']);
    }

    public function test_all_top_level_zazu_skeleton_pages_are_reachable(): void
    {
        $routes = [
            'dashboard',
            'quotes.index',
            'calendar.index',
            'suppliers.index',
            'inventory.index',
            'assets.index',
            'reports.index',
            'settings.index',
            'capabilities.index',
            'work.index',
            'customers.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));

            $response->assertOk();
        }
    }

    public function test_key_top_level_and_create_pages_are_reachable(): void
    {
        foreach ([
            'onboarding.index',
            'onboarding.catalogue',
            'onboarding.experience',
            'onboarding.business',
            'finance.index',
            'finance.invoices.create',
            'finance.payments.create',
            'finance.expenses.create',
            'suppliers.index',
            'suppliers.create',
            'purchasing.index',
            'purchasing.create',
            'inventory.index',
            'inventory.create',
            'assets.index',
            'assets.create',
            'reports.index',
            'settings.index',
            'settings.compliance',
            'capabilities.index',
            'capabilities.create',
            'work.index',
            'work.create',
            'customers.index',
            'customers.create',
        ] as $route) {
            $response = $this->get(route($route));

            $this->assertContains(
                $response->status(),
                [200, 302],
                "Expected {$route} to be directly reachable or intentionally redirected."
            );
        }
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function test_core_record_pages_render_with_representative_business_data(): void
    {
        $business = \App\Models\Business::create([
            'name' => 'Render Business',
            'slug' => 'render-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($business);

        $customer = \App\Models\Customer::create([
            'business_id' => $business->id,
            'name' => 'Render Customer',
        ]);

        $event = \App\Models\Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'JOB-RENDER-001',
            'name' => 'Render Wedding',
            'event_date' => now()->addDays(7)->toDateString(),
            'status' => 'confirmed',
        ]);

        $capability = \App\Models\BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Render Catering',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'default_unit' => 'event',
            'is_active' => true,
        ]);

        $requirement = \App\Models\EventRequirement::create([
            'event_id' => $event->id,
            'capability_id' => $capability->id,
            'description' => 'Render catering service',
            'category' => 'Catering',
            'quantity' => '1.00',
            'unit' => 'event',
            'status' => 'open',
        ]);

        $quote = \App\Models\Quote::create([
            'event_id' => $event->id,
            'reference' => 'QUO-RENDER-001',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);

        $version = $quote->versions()->create([
            'version' => 1,
            'status' => 'draft',
            'subtotal' => '1500.00',
            'tax_total' => '225.00',
            'total' => '1725.00',
        ]);

        $version->items()->create([
            'event_requirement_id' => $requirement->id,
            'capability_id' => $capability->id,
            'description' => 'Render catering service',
            'quantity' => '1.00',
            'unit' => 'event',
            'unit_price' => '1500.00',
            'line_total' => '1500.00',
            'pricing_basis' => 'custom',
            'source_snapshot' => [
                'description' => 'Render catering service',
                'category' => 'Catering',
                'quantity' => '1.00',
                'unit' => 'event',
                'notes' => null,
                'capability_id' => $capability->id,
            ],
        ]);

        $supplier = \App\Models\Supplier::create([
            'business_id' => $business->id,
            'name' => 'Render Supplier',
        ]);

        $purchaseOrder = \App\Models\PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $supplier->id,
            'reference' => 'PO-RENDER-001',
            'status' => 'draft',
            'currency' => 'ZAR',
            'total_amount' => '500.00',
        ]);

        $purchaseOrder->items()->create([
            'business_id' => $business->id,
            'description' => 'Render stock',
            'quantity' => '5.00',
            'unit' => 'kg',
            'unit_price' => '100.00',
            'line_total' => '500.00',
        ]);

        $inventoryItem = \App\Models\InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Render stock',
            'unit' => 'kg',
            'reorder_level' => '2.00',
        ]);

        $inventoryItem->movements()->create([
            'business_id' => $business->id,
            'type' => 'receipt',
            'quantity' => '5.00',
            'unit_cost' => '100.00',
            'movement_date' => now()->toDateString(),
        ]);

        $asset = \App\Models\Asset::create([
            'business_id' => $business->id,
            'asset_tag' => 'ASSET-RENDER-001',
            'name' => 'Render Tent',
            'status' => 'available',
            'condition' => 'good',
            'currency' => 'ZAR',
            'purchase_cost' => '3000.00',
        ]);

        $invoice = \App\Models\Invoice::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'quote_id' => $quote->id,
            'quote_version_id' => $version->id,
            'number' => 'INV-RENDER-001',
            'status' => 'issued',
            'currency' => 'ZAR',
            'subtotal' => '1500.00',
            'tax_total' => '225.00',
            'total' => '1725.00',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
        ]);

        $invoice->items()->create([
            'description' => 'Render catering service',
            'quantity' => '1.00',
            'unit' => 'event',
            'unit_price' => '1500.00',
            'line_total' => '1500.00',
            'quote_item_id' => $version->items()->first()->id,
        ]);

        foreach ([
            ['work.show', [$event]],
            ['work.edit', [$event]],
            ['work.travel.index', [$event]],
            ['work.travel.create', [$event]],
            ['work.costs.index', [$event]],
            ['work.costs.create', [$event]],
            ['work.preparation.index', [$event]],
            ['work.preparation.create', [$event]],
            ['work.quotes.index', [$event]],
            ['work.quotes.create', [$event]],
            ['work.requirements.index', [$event]],
            ['work.requirements.create', [$event]],
            ['customers.show', [$customer]],
            ['customers.edit', [$customer]],
            ['quotes.show', [$quote]],
            ['quotes.versions.edit', [$quote, $version]],
            ['finance.invoices.show', [$invoice]],
            ['purchasing.show', [$purchaseOrder]],
            ['assets.index', []],
            ['inventory.index', []],
        ] as [$route, $parameters]) {
            $this->get(route($route, $parameters))
                ->assertOk();
        }

        $this->assertTrue($asset->exists);
    }

    public function test_app_shell_uses_flat_navigation_and_richer_account_surface(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertDontSee('Active workspace')
            ->assertDontSee('zazu-nav-icon')
            ->assertDontSee('zazu-nav-arrow')
            ->assertSee('Signed in')
            ->assertSee('Experience preference')
            ->assertSee('Business settings');
    }

    public function test_dashboard_links_to_every_top_level_module(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();

        foreach ([
            'work.index',
            'customers.index',
            'quotes.index',
            'calendar.index',
            'suppliers.index',
            'inventory.index',
            'assets.index',
            'reports.index',
            'settings.index',
            'capabilities.index',
        ] as $route) {
            $response->assertSee(route($route), false);
        }
    }
}

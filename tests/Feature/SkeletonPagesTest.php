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
        $this->signInAsOwner();
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
            $this->get(route($route))->assertOk();
        }
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

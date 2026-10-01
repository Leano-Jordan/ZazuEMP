<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    public function test_shared_layout_exposes_accessible_navigation_and_theme_controls(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('<a class="zazu-skip-link" href="#main-content">Skip to main content</a>', false);
        $response->assertSee('<main id="main-content" class="zazu-main" tabindex="-1">', false);
        $response->assertSee('aria-label="Primary"', false);
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('data-theme-icon-sun', false);
        $response->assertSee('data-theme-icon-moon', false);
        $response->assertSee('aria-pressed="false"', false);
        $response->assertSee('data-user-menu', false);
        $response->assertSee('data-user-trigger', false);
        $response->assertSee('data-user-popover', false);
        $response->assertSee('Sign out', false);
        $response->assertSee('name="viewport"', false);
        $response->assertSee('viewport-fit=cover', false);
        $response->assertSee('>Jobs</a>', false);
        $response->assertSee('>Customers</a>', false);
        $response->assertSee('>Services &amp; prices</a>', false);
        $response->assertSee('>Quotes</a>', false);
        $response->assertSee('>Calendar</a>', false);
        $response->assertSee('>Purchasing</a>', false);
        $response->assertSee('>Suppliers</a>', false);
        $response->assertSee('>Inventory</a>', false);
        $response->assertSee('>Assets</a>', false);
        $response->assertSee('>Finance</a>', false);
        $response->assertSee('>Search</a>', false);
        $response->assertSee('>Reports</a>', false);

        $html = $response->getContent();
        $themePosition = strpos($html, 'data-theme-toggle');

        $this->assertNotFalse($themePosition);
    }

    public function test_primary_navigation_exposes_real_destinations(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('aria-label="Primary"', false);
        $response->assertSee('href="'.route('suppliers.index').'"', false);
        $response->assertSee('href="'.route('purchasing.index').'"', false);
        $response->assertSee('href="'.route('inventory.index').'"', false);
        $response->assertSee('href="'.route('assets.index').'"', false);
        $response->assertSee('href="'.route('quotes.index').'"', false);
    }

    public function test_hierarchical_navigation_exposes_primary_areas_and_children(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Workspace')
            ->assertSee('Sales &amp; operations', false)
            ->assertSee('Purchasing &amp; resources', false)
            ->assertSee('Money &amp; control', false)
            ->assertSee('System')
            ->assertSee('data-zazu-nav-trigger', false)
            ->assertSee('data-zazu-nav-panel', false)
            ->assertSee('Settings')
            ->assertSee('Suppliers')
            ->assertSee('Quotes');
    }

    

    public function test_quote_register_uses_semantic_status_classes(): void
    {
        $view = file_get_contents(resource_path('views/quotes/index.blade.php'));

        $this->assertStringContainsString("zazu-chip-danger", $view);
        $this->assertStringContainsString("declined", $view);
        $this->assertStringContainsString("expired", $view);
    }

    public function test_customer_form_uses_mobile_friendly_contact_inputs(): void
    {
        $response = $this->get(route('customers.create'));

        $response->assertOk();
        $response->assertSee('type="tel"', false);
        $response->assertSee('autocomplete="tel"', false);
        $response->assertSee('type="email"', false);
        $response->assertSee('autocomplete="email"', false);
    }

    public function test_key_forms_keep_required_controls_and_error_help_available(): void
    {
        $response = $this->get(route('work.create'));

        $response->assertOk();
        $response->assertSee('required', false);
        $response->assertSee('data-error-summary', false);
        $response->assertSee('data-mobile-sidebar', false);
        $response->assertSee('data-mobile-sidebar-toggle', false);
        $response->assertSee('aria-controls="zazu-mobile-sidebar"', false);
        $response->assertSee('data-mobile-sidebar-close', false);
        $response->assertDontSee('data-mobile-nav', false);
    }
    public function test_settings_page_renders_without_malformed_closing_markup(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertOk();
        $response->assertDontSee('</div>>', false);
        $response->assertSee('Save business appearance', false);
        $response->assertSee('Open activity audit', false);
    }


}

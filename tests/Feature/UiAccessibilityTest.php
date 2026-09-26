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
        $response->assertSee('aria-pressed="false"', false);
        $response->assertSee('name="viewport"', false);
        $response->assertSee('viewport-fit=cover', false);
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
        $response->assertSee('aria-label="Mobile primary"', false);
    }
}

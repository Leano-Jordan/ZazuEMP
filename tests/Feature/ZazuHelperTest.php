<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZazuHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_workspace_exposes_the_zazu_helper_by_default(): void
    {
        $this->signInAsOwner();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Zazu guide')
            ->assertSee('Turn guide off')
            ->assertSee('aria-expanded="false"', false)
            ->assertDontSee('data-zazu-helper-toggle aria-expanded="false" aria-pressed', false)
            ->assertSee('data-zazu-guide-enabled="on"', false);
    }

    public function test_public_landing_authentication_modal_does_not_render_the_workspace_helper(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('data-auth-modal', false)
            ->assertDontSee('data-zazu-helper', false);
    }

    public function test_helper_moves_only_for_work_and_sales_navigation_popovers(): void
    {
        $script = file_get_contents(resource_path('js/app.js'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('zazu-nav-workspace', $script);
        $this->assertStringContainsString('zazu-nav-sales', $script);
        $this->assertStringContainsString('moveHelperForNav', $script);
        $this->assertStringContainsString('getBoundingClientRect()', $script);
        $this->assertStringContainsString('mouseenter', $script);
        $this->assertStringContainsString('focusin', $script);
        $this->assertStringContainsString('is-nav-avoiding', $css);
        $this->assertStringContainsString('--zazu-helper-nav-right', $css);
        $this->assertStringContainsString('width: 46px;', $css);
    }

}

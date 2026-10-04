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

    public function test_helper_collision_and_motion_hooks_are_present_in_the_workspace_bundle(): void
    {
        $script = file_get_contents(resource_path('js/app.js'));
        $css = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));

        $this->assertStringContainsString('maxSafeRight', $script);
        $this->assertStringContainsString('is-nav-avoiding', $script);
        $this->assertStringContainsString('5 * 1024 * 1024', $script);
        $this->assertStringContainsString('transform: translate3d(10px, -2px, 0) scale(.985);', $css);
        $this->assertStringNotContainsString('window.innerWidth - flyoutRect.left + 14', $script);
    }

}

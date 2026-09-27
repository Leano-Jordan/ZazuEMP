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
            ->assertSee('data-zazu-guide-enabled="on"', false);
    }

    public function test_authentication_pages_do_not_render_the_workspace_helper(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('data-zazu-helper', false);
    }
}

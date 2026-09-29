<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_manage_landing_media(): void
    {
        $admin = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_emails', ['admin@zazu.test']);

        $this->actingAs($admin)
            ->get(route('admin.landing.settings'))
            ->assertOk()
            ->assertSee('Landing page imagery');

        $this->actingAs($admin)
            ->put(route('admin.landing.settings.update'), [
                'hero_image' => 'sound_stage',
                'operations_image' => 'event_catering',
                'resources_image' => 'sound_stage',
                'control_image' => 'wedding_catering',
            ])
            ->assertRedirect(route('admin.landing.settings'));

        $row = DB::table('platform_landing_settings')->first();

        $this->assertSame(config('zazu.landing_image_library.sound_stage.url'), $row->hero_image_path);
        $this->assertSame(config('zazu.landing_image_library.event_catering.url'), $row->operations_image_path);
        $this->assertSame(config('zazu.landing_image_library.wedding_catering.url'), $row->control_image_path);
    }

    public function test_non_platform_admin_cannot_manage_landing_media(): void
    {
        $user = \App\Models\User::factory()->create(['email' => 'owner@zazu.test']);
        Config::set('zazu.platform_admin_emails', ['admin@zazu.test']);

        $this->actingAs($user)
            ->get(route('admin.landing.settings'))
            ->assertForbidden();
    }
}

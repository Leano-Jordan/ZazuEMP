<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InterfaceRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function workspace(): array
    {
        $business = Business::create([
            'name' => 'Regression Business',
            'slug' => 'regression-' . Str::lower(Str::random(6)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user, ['role' => 'owner']);

        return [$business, $user];
    }

    public function test_calendar_page_loads_and_shows_scheduled_work(): void
    {
        [$business, $user] = $this->workspace();

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Calendar Test Customer',
        ]);

        Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'CAL-REGRESSION',
            'name' => 'Calendar Regression Job',
            'event_type' => 'Catering order',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get(route('calendar.index', [
            'view' => 'agenda',
            'month' => now()->format('Y-m'),
        ]));

        $response->assertOk()
            ->assertSee('Calendar Regression Job')
            ->assertSee('Agenda');
    }

    public function test_calendar_month_view_renders_holiday_cells_without_missing_day_state(): void
    {
        [$business, $user] = $this->workspace();

        $response = $this->actingAs($user)->get(route('calendar.index', [
            'month' => '2026-04',
            'view' => 'month',
        ]));

        $response->assertOk()
            ->assertSee('Freedom Day')
            ->assertSee('Good Friday')
            ->assertSee('zazu-calendar-grid');
    }

    public function test_calendar_mobile_month_view_preserves_all_seven_columns_and_holiday_markers(): void
    {
        $view = file_get_contents(resource_path('views/calendar/index.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('zazu-calendar-grid-scroll', $view);
        $this->assertStringContainsString('has-holiday', $view);
        $this->assertStringContainsString('zazu-calendar-holiday-marker', $view);
        $this->assertStringContainsString('grid-template-columns: repeat(7, minmax(104px, 1fr));', $css);
        $this->assertStringContainsString('min-width: 728px;', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(7, minmax(92px, 1fr));', $css);
        $this->assertStringContainsString('.zazu-calendar-cell.has-holiday', $css);
        $this->assertStringContainsString('.zazu-calendar-holiday-marker', $css);
    }

    public function test_calendar_keeps_work_navigation_active_for_mobile_drawer_recovery(): void
    {
        [$business, $user] = $this->workspace();

        $response = $this->actingAs($user)->get(route('calendar.index'));

        $response->assertOk()
            ->assertSee('class="zazu-nav-area is-active" data-zazu-nav-area', false)
            ->assertSee('class="zazu-nav-link active" aria-current="page">Calendar</a>', false);
    }

    public function test_calendar_permission_is_explicit_for_configured_operational_roles(): void
    {
        [$business] = $this->workspace();

        foreach (['staff', 'manager'] as $role) {
            $user = User::factory()->create();
            $business->users()->attach($user, ['role' => $role]);

            $this->assertContains(
                'calendar.view',
                config('zazu.permissions.roles.'.$role, []),
                "Role [{$role}] must explicitly retain calendar.view."
            );

            $this->actingAs($user)
                ->get(route('calendar.index'))
                ->assertOk();
        }
    }

    public function test_calendar_navigation_has_a_server_rendered_mobile_recovery_contract(): void
    {
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString(
            "request()->routeIs('dashboard','work.*','customers.*','calendar.*')",
            $layout
        );
        $this->assertStringContainsString('@media (max-width: 850px)', $mobileCss);
        $this->assertStringContainsString(
            '.zazu-nav-area.is-active > .zazu-nav-flyout',
            $mobileCss
        );
        $this->assertStringContainsString(
            "window.matchMedia('(max-width: 850px)')",
            $js
        );
    }

    public function test_cross_theme_ui_contracts_reject_known_light_on_light_and_dark_on_dark_regressions(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $visualCss = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));
        $offline = file_get_contents(resource_path('views/offline.blade.php'));

        $this->assertStringContainsString(':root[data-theme="dark"] .zazu-btn-danger', $appCss);
        $this->assertStringContainsString('background: var(--zazu-danger-soft);', $appCss);
        $this->assertStringContainsString('color: var(--zazu-danger-ink);', $appCss);

        $this->assertStringNotContainsString('background:var(--zazu-primary); color:#fff;', $visualCss);
        $this->assertStringNotContainsString('background:var(--zazu-primary);color:#fff;', $visualCss);
        $this->assertStringContainsString('color:var(--zazu-primary-ink);', $visualCss);
        $this->assertStringContainsString('color: var(--zazu-success-ink);', $visualCss);

        $this->assertStringContainsString(':root{color-scheme:dark;', $offline);
        $this->assertStringContainsString('background:linear-gradient(135deg,var(--surface),var(--surface2) 72%)', $offline);
        $this->assertStringContainsString('color:var(--ink)', $offline);

        $this->assertStringContainsString('@media (max-width: 850px)', $mobileCss);
    }

    public function test_resource_register_pages_render_without_horizontal_layout_assumptions(): void
    {
        [$business, $user] = $this->workspace();

        $assets = $this->actingAs($user)->get(route('assets.index'));
        $assets->assertOk()
            ->assertSee('Asset register')
            ->assertSee('Add asset')
            ->assertSee('zazu-assets-directory');

        $inventory = $this->actingAs($user)->get(route('inventory.index'));
        $inventory->assertOk()
            ->assertSee('Stock register')
            ->assertSee('New inventory item')
            ->assertSee('zazu-inventory-directory');
    }

    public function test_navigation_places_calendar_under_work_and_uses_compact_group_labels(): void
    {
        [$business, $user] = $this->workspace();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('>Work<', false)
            ->assertSee('>Sales<', false)
            ->assertSee('>Resources<', false)
            ->assertSee('>Finance<', false)
            ->assertSee('>Calendar<', false)
            ->assertSee('>Setup<', false)
            ->assertDontSee('Sales &amp; operations', false)
            ->assertDontSee('Purchasing &amp; resources', false)
            ->assertDontSee('Money &amp; control', false);
    }

    public function test_resource_register_layout_keeps_content_distributed_and_actions_bounded(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('grid-template-columns: minmax(260px, 1.2fr) minmax(130px, .55fr) minmax(0, 2.3fr);', $css);
        $this->assertStringContainsString('.zazu-assets-directory .zazu-action-group {', $css);
        $this->assertStringContainsString('.zazu-inventory-directory .zazu-action-group {', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(150px, 1.25fr) repeat(3, minmax(88px, 1fr)) auto;', $css);
        $this->assertStringNotContainsString('justify-content: flex-end;\n    gap: 8px;\n    padding-left: 16px;', $css);
    }

    public function test_shared_page_banners_inherit_workspace_state_visual_contract(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('var(--zazu-dashboard-image)', $css);
        $this->assertStringContainsString('.zazu-command-band .zazu-eyebrow', $css);
        $this->assertStringContainsString('.zazu-command-meta {', $css);
        $this->assertStringContainsString('background: rgba(2, 15, 27, .44);', $css);        $sweep = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('.zazu-command-band', $sweep);
        $this->assertStringContainsString('protected shared banner contrast', $sweep);
        $this->assertStringNotContainsString(':where(.zazu-command-band, .zazu-page-header, .zazu-dashboard-command)', $sweep);

    }

    public function test_mobile_header_uses_one_explicit_grid_contract_and_catalogue_tabs_remain_readable_in_dark_mode(): void
    {
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));
        $visualCss = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('"menu title theme"', $mobileCss);
        $this->assertStringContainsString('"search search search"', $mobileCss);
        $this->assertStringContainsString('"tabs tabs tabs tabs"', $mobileCss);
        $this->assertStringContainsString('"page-action page-action page-action"', $mobileCss);
        $this->assertStringNotContainsString('grid-template-areas: "menu context actions";', $mobileCss);
        $this->assertStringNotContainsString('left: 56px;', $mobileCss);
        $this->assertStringNotContainsString('min-height: 112px;', $mobileCss);

        $this->assertStringContainsString('[data-theme="dark"] .zazu-catalogue-tab {', $visualCss);
        $this->assertStringContainsString('color:var(--zazu-text-main);', $visualCss);
        $this->assertStringContainsString('.zazu-catalogue-command > :first-child {', $visualCss);
        $this->assertStringContainsString('padding:20px 18px 18px;', $visualCss);
    }

    public function test_helper_moves_out_of_mobile_navigation_without_disappearing(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('body.zazu-mobile-menu-open .zazu-helper', $css);
        $this->assertStringContainsString('translateX(calc(100% + 28px))', $css);
        $this->assertStringContainsString('opacity: 1;', $css);
        $this->assertStringNotContainsString('body.zazu-mobile-menu-open .zazu-helper {\n    opacity: 0;', $css);
    }

    public function test_helper_moves_for_work_and_sales_popovers(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('.zazu-helper.is-nav-avoiding', $css);
        $this->assertStringContainsString('--zazu-helper-nav-right', $css);
        $this->assertStringContainsString("new Set(['zazu-nav-workspace', 'zazu-nav-sales'])", $js);
        $this->assertStringContainsString('getBoundingClientRect()', $js);
        $this->assertStringContainsString('helper.style.setProperty', $js);
    }

    public function test_helper_is_docked_without_runtime_collision_repositioning(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('.zazu-helper {', $css);
        $this->assertStringContainsString('z-index: 50;', $css);
        $this->assertStringContainsString('width: 46px;', $css);
        $this->assertStringContainsString('body.zazu-mobile-menu-open .zazu-helper', $css);
        $this->assertStringNotContainsString('syncHelperCollision', $js);
        $this->assertStringContainsString('is-nav-avoiding', $css);
        $this->assertStringNotContainsString('scheduleHelperCollisionSync', $js);
    }

    public function test_mobile_search_does_not_collapse_at_520px_and_active_nav_keeps_sidebar_contrast(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));

        $this->assertStringContainsString('color:var(--zazu-sidebar-ink)', $appCss);
        $this->assertStringContainsString('.zazu-command-search {', $mobileCss);
        $this->assertStringContainsString('width: 100%;', $mobileCss);
        $this->assertStringNotContainsString('.zazu-command-search {\n        width: 40px;', $mobileCss);
    }

    public function test_commercial_visual_system_has_glass_depth_rich_light_surfaces_and_non_dark_mobile_search(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $sweepCss = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));

        $this->assertStringContainsString('--zazu-page: #D9E8F5;', $appCss);
        $this->assertStringContainsString('--zazu-surface: #F2F7FC;', $appCss);
        $this->assertStringContainsString('backdrop-filter: blur(10px) saturate(110%);', $appCss);
        $this->assertStringContainsString('.zazu-btn-cta {', $sweepCss);
        $this->assertStringContainsString('background: linear-gradient(135deg, var(--zazu-primary), var(--zazu-primary-deep));', $sweepCss);
        $this->assertStringContainsString('rgba(15,23,42,.43)', $sweepCss);
        $this->assertStringContainsString('"search search search"', $mobileCss);
        $this->assertStringContainsString('width: 100%;', $mobileCss);
    }

    public function test_public_landing_has_explicit_foreground_surface_pairings_and_balanced_plan_delivery(): void
    {
        $view = file_get_contents(resource_path('views/landing.blade.php'));

        $this->assertStringContainsString('.brand strong{color:#12324D}', $view);
        $this->assertStringContainsString('.topnav .register,.topnav .login{', $view);
        $this->assertStringContainsString('.telemetry small,.record small,.record-label{color:#496A83}', $view);
        $this->assertStringContainsString('.compare>article+article{background:#0B2A40;color:#F5FAFF}', $view);
        $this->assertStringContainsString('.mark{background:#126BD8;color:#FFFFFF}', $view);
        $this->assertStringContainsString('.btn.primary{background:#126BD8;border-color:#126BD8;color:#FFFFFF}', $view);
        $this->assertStringContainsString('.feature.mini:nth-of-type(4){grid-column:1 / span 6;grid-row:3}', $view);
        $this->assertStringContainsString('.feature.mini:nth-of-type(5){grid-column:7 / -1;grid-row:3}', $view);
    }

}

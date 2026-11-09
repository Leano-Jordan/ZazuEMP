<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiProportionFoundationTest extends TestCase
{
    public function test_canonical_visual_tokens_match_the_coastal_blue_reference_family_and_disciplined_proportions(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--zazu-primary: #159DD2;', $css);
        $this->assertStringContainsString('--zazu-primary-soft: #DDF4FC;', $css);
        $this->assertStringContainsString('--zazu-primary-deep: #087FA8;', $css);
        $this->assertStringContainsString('--zazu-page: #F5FBFE;', $css);
        $this->assertStringContainsString('--zazu-sidebar: #8ED8F8;', $css);
        $this->assertStringContainsString('--zazu-primary-light: #35B9E8;', $css);
        $this->assertStringContainsString('--zazu-accent: #49C7B8;', $css);
        $this->assertStringContainsString('--zazu-page-deep: #DDF4FC;', $css);
        $this->assertStringContainsString('--zazu-ui-control-height: 44px;', $css);
        $this->assertStringContainsString('--zazu-content-max: 1320px;', $css);
        $this->assertStringContainsString('--zazu-ui-radius: var(--zazu-radius-lg);', $css);
        $this->assertStringContainsString('--zazu-radius-lg: 10px;', $css);
    }

    public function test_topbar_uses_a_light_workspace_surface_instead_of_repeating_the_sidebar_colour(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        preg_match_all('/\\.zazu-topbar\\s*\\{([^}]*)\\}/s', $css, $rules);
        $this->assertNotEmpty($rules[1]);

        $finalRule = end($rules[1]);
        $this->assertStringContainsString('var(--zazu-surface)', $finalRule);
        $this->assertStringContainsString('var(--zazu-page)', $finalRule);
        $this->assertStringNotContainsString('var(--zazu-sidebar)', $finalRule);
        $this->assertStringContainsString('var(--zazu-accent)', $finalRule);
    }

    public function test_canonical_visual_layer_has_readable_controls_and_real_interaction_hierarchy(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));
        $appCss = file_get_contents(resource_path('css/app.css'));
        $responsiveCss = file_get_contents(resource_path('css/zazu-responsive-theme.css'));

        $this->assertIsString($css);
        $this->assertIsString($appCss);
        $this->assertIsString($responsiveCss);
        $this->assertStringContainsString('.zazu-label,', $css);
        $this->assertStringContainsString('    font-size: 13.5px;', $css);
        $this->assertStringContainsString('    min-height: 44px;', $css);
        $this->assertStringContainsString('.zazu-section-tabs {', $responsiveCss);
        $this->assertStringContainsString('    line-height: 1.3;', $css);
        $this->assertStringContainsString('.zazu-btn-primary {', $appCss);
        $this->assertStringContainsString('    background: var(--zazu-primary);', $appCss);
    }

    public function test_dashboard_hero_final_rule_uses_theme_aware_surface_tokens(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        preg_match_all('/\\.zazu-dash-hero\\s*\\{([^}]*)\\}/s', $css, $heroRules);
        $this->assertNotEmpty($heroRules[1]);

        $finalHeroRule = end($heroRules[1]);
        $this->assertNotEmpty(array_filter(
            $heroRules[1],
            fn (string $rule): bool => str_contains($rule, 'var(--zazu-blue-brand)')
                && str_contains($rule, 'var(--zazu-card-bg)')
        ));
        $this->assertStringNotContainsString('#FFFFFF', $finalHeroRule);

        foreach ($heroRules[1] as $heroRule) {
            $this->assertStringNotContainsString('#FFFFFF', $heroRule);
        }
    }

    public function test_dashboard_does_not_render_midnight_as_an_event_start_time(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString("format('d') ?: 'TBC'", $dashboard);
        $this->assertStringContainsString("format('M') ?: 'DATE'", $dashboard);
        $this->assertStringContainsString("format('d F Y')", $dashboard);
        $this->assertStringNotContainsString("format('H:i')", $dashboard);
    }

    public function test_dashboard_kpi_order_matches_the_reference_scan_path(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $active = strpos($dashboard, '<span>Active events</span>');
        $quotes = strpos($dashboard, '<span>Draft quotes</span>');
        $customers = strpos($dashboard, '<span>Customers</span>');
        $upcoming = strpos($dashboard, '<span>Next 14 days</span>');
        $this->assertNotFalse($active);
        $this->assertNotFalse($quotes);
        $this->assertNotFalse($customers);
        $this->assertNotFalse($upcoming);
        $this->assertLessThan($quotes, $active);
        $this->assertLessThan($customers, $quotes);
        $this->assertLessThan($upcoming, $customers);
    }

    public function test_dashboard_prioritises_quote_kpi_and_uses_invoice_kpi_only_as_a_fallback(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $quotesPosition = strpos($dashboard, "@if(\$workspaceTools['quotes'])");
        $invoicesPosition = strpos($dashboard, "@elseif(\$workspaceTools['finance'] && \$experienceLevel !== 'basic')");
        $this->assertNotFalse($quotesPosition);
        $this->assertNotFalse($invoicesPosition);
        $this->assertLessThan($invoicesPosition, $quotesPosition);
        $this->assertStringContainsString('zazu-dash-metric-icon', substr($dashboard, $invoicesPosition, 900));
    }

    public function test_dashboard_welcome_and_capability_callout_follow_the_supplied_reference(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);
        $this->assertStringContainsString('zazu-dash-greeting', $dashboard);
        $this->assertStringContainsString('auth()->user()->name', $dashboard);
        $this->assertStringContainsString('Also in this business', $dashboard);
        $this->assertStringContainsString('Zazu keeps these capabilities available', $dashboard);
        $this->assertStringNotContainsString('Workspace state', $dashboard);
        $this->assertStringContainsString('.zazu-dash-capability {', $css);
        $this->assertStringContainsString('.zazu-dash-capability-art,', $css);
        $this->assertStringContainsString('@media (max-width: 620px)', $css);
    }

    public function test_navigation_uses_compact_geometry_and_explicit_first_paint_contrast(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $dashboardCss = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));

        $this->assertIsString($appCss);
        $this->assertIsString($dashboardCss);
        $this->assertStringContainsString('.zazu-nav-area-trigger {', $appCss);
        $this->assertStringContainsString('border-radius: 4px;', $appCss);
        $this->assertStringContainsString('color: var(--zazu-sidebar-ink);', $appCss);
        $this->assertStringContainsString('background: var(--zazu-sidebar-hover);', $appCss);
        $this->assertStringContainsString('background: #29445D !important;', $dashboardCss);
        $this->assertStringContainsString('color: #FFFFFF !important;', $dashboardCss);
        $this->assertStringContainsString('border-radius: 4px !important;', $dashboardCss);
        $this->assertStringContainsString('outline: 2px solid #8CCDE4 !important;', $dashboardCss);
        $this->assertStringNotContainsString('background: #B9CAD9 !important;', $dashboardCss);
        $this->assertStringNotContainsString('background: rgba(255,255,255,.09) !important;', $dashboardCss);
    }

}

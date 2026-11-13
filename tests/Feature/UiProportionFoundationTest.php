<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiProportionFoundationTest extends TestCase
{
    public function test_canonical_visual_tokens_match_the_coastal_blue_reference_family_and_disciplined_proportions(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--zazu-primary: #426987;', $css);
        $this->assertStringContainsString('--zazu-primary-soft: #E1E9F0;', $css);
        $this->assertStringContainsString('--zazu-primary-deep: #304B63;', $css);
        $this->assertStringContainsString('--zazu-page: #DCE5ED;', $css);
        $this->assertStringContainsString('--zazu-sidebar: #D5E0E8;', $css);
        $this->assertStringContainsString('--zazu-primary-light: #6487A2;', $css);
        $this->assertStringContainsString('--zazu-accent: #6487A2;', $css);
        $this->assertStringContainsString('--zazu-page-deep: #E8EEF3;', $css);
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
        $this->assertStringContainsString('background: #B9CCD9 !important;', $dashboardCss);
        $this->assertStringContainsString('color: #20374A !important;', $dashboardCss);
        $this->assertStringContainsString('border-radius: 4px !important;', $dashboardCss);
        $this->assertStringContainsString('outline: 2px solid #426987 !important;', $dashboardCss);
        $this->assertStringNotContainsString('background: #B9CAD9 !important;', $dashboardCss);
        $this->assertStringNotContainsString('background: rgba(255,255,255,.09) !important;', $dashboardCss);
        $productCss = file_get_contents(resource_path('css/zazu-product-authority.css'));
        $this->assertIsString($productCss);
        $this->assertStringContainsString('background: #B4C8D7 !important;', $productCss);
        $this->assertStringContainsString('background: #486174 !important;', $productCss);
        $this->assertStringContainsString('color: #F3F6F8 !important;', $productCss);
        $this->assertStringContainsString('html:not([data-theme="dark"]) .zazu-sidebar .zazu-footer-workspace {', $productCss);
        $this->assertStringContainsString('color: #536B7F !important;', $productCss);
        $this->assertStringContainsString('html[data-theme="dark"] .zazu-sidebar .zazu-footer-workspace {', $productCss);
        $this->assertStringContainsString('color: #B8C7D2 !important;', $productCss);

        $this->assertStringContainsString('background: var(--zazu-surface-3) !important;', $productCss);
        $this->assertStringNotContainsString('body[data-zazu-route^="finance."] .zazu-form-section {', $productCss);

    }


    public function test_product_wide_visual_authority_is_loaded_after_module_styles_and_applies_shared_primitives(): void
    {
        $css = file_get_contents(resource_path('css/zazu-product-authority.css'));
        $tokens = file_get_contents(resource_path('css/app.css'));
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $vite = file_get_contents(base_path('vite.config.js'));

        $this->assertIsString($css);
        $this->assertIsString($tokens);
        $this->assertIsString($layout);
        $this->assertIsString($vite);
        $this->assertStringContainsString('zazu-product-authority.css', $layout);
        $this->assertStringContainsString('zazu-product-authority.css', $vite);
        $this->assertGreaterThan(
            strpos($layout, 'zazu-dashboard-authority.css'),
            strpos($layout, 'zazu-product-authority.css')
        );
        $this->assertStringContainsString('--zazu-border: #AEBBC7;', $tokens);
        $this->assertStringContainsString('--zazu-border: #294057;', $tokens);
        $this->assertStringNotContainsString(':root {', $css);
        $this->assertStringNotContainsString(':root[data-theme="dark"] {', $css);
        $this->assertStringContainsString('.zazu-card, .zazu-panel', $css);
        $this->assertStringContainsString('.zazu-form-section', $css);
        $this->assertStringContainsString('.zazu-table-wrap', $css);
        $this->assertStringContainsString('.zazu-btn {', $css);
        $this->assertStringContainsString('.zazu-input, .zazu-select, .zazu-textarea', $css);
        $this->assertStringContainsString('.zazu-list-item:hover {', $css);
        $this->assertStringContainsString('transform: none;', $css);
        $this->assertStringContainsString('.zazu-metric-card:hover {', $css);
        $this->assertStringContainsString('.zazu-document-table-wrap', $css);
        $this->assertStringContainsString('.zazu-empty {', $css);
        $this->assertStringContainsString('.zazu-dashboard-quickbar nav a', $css);
        $this->assertStringContainsString('.zazu-workload-pill', $css);
        $this->assertStringContainsString('border-radius: var(--zazu-radius-md);', $css);
        $this->assertStringContainsString('.zazu-editor {', $css);
        $this->assertStringContainsString('.zazu-form-section + .zazu-form-section', $css);
        $this->assertStringContainsString('.zazu-actionbar-sticky {', $css);
        $this->assertStringContainsString('env(safe-area-inset-bottom)', $css);
        $this->assertStringContainsString('.zazu-form-aside {', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr);', $css);
    }




}

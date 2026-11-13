<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiShellThemeRegressionTest extends TestCase
{
    public function test_theme_defaults_to_reference_light_while_preserving_explicit_saved_choice(): void
    {
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $appJs = file_get_contents(resource_path('js/app.js'));

        $this->assertIsString($layout);
        $this->assertIsString($appJs);

        $this->assertStringContainsString(
            "saved === 'light' || saved === 'dark' ? saved : 'light'",
            $layout
        );
        $this->assertStringContainsString("return 'light';", $appJs);
        $this->assertStringNotContainsString("prefers-color-scheme: dark", $layout);
        $this->assertStringNotContainsString("prefers-color-scheme: dark", $appJs);
        $this->assertStringContainsString("safeStorageSet(storageKey, next);", $appJs);
    }

    public function test_light_shell_uses_blue_navigation_and_separate_light_topbar_surfaces(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--zazu-sidebar: #D5E0E8;', $css);
        $this->assertStringContainsString('--zazu-sidebar-ink: #23394D;', $css);
        $this->assertStringContainsString('--zazu-sidebar-active: #B4C8D7;', $css);
        $this->assertStringContainsString('background: color-mix(in srgb, var(--zazu-surface) 94%, var(--zazu-page));', $css);
        $this->assertStringContainsString('color: var(--zazu-ink);', $css);
        $this->assertStringContainsString(':root[data-theme="dark"] {', $css);
        $this->assertStringContainsString('--zazu-sidebar: #202B35;', $css);
    }

    public function test_light_sidebar_navigation_states_use_the_blue_shell_palette(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('background: var(--zazu-sidebar-hover);', $css);
        $this->assertStringContainsString('background: var(--zazu-sidebar-surface);', $css);
        $this->assertStringContainsString('color: var(--zazu-sidebar-muted);', $css);
        $this->assertStringContainsString('box-shadow: inset 3px 0 0 var(--zazu-sidebar-active);', $css);
    }

    public function test_dashboard_metric_tiles_use_soft_semantic_surface_tints(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.zazu-dash-metrics > .zazu-dash-metric:nth-child(4n + 1)', $css);
        $this->assertStringContainsString('var(--zazu-info-soft)', $css);
        $this->assertStringContainsString('var(--zazu-success-soft)', $css);
        $this->assertStringContainsString('var(--zazu-primary-soft)', $css);
        $this->assertStringContainsString('var(--zazu-warning-soft)', $css);
        $this->assertStringContainsString('var(--zazu-card-bg)', $css);

        $this->assertStringContainsString('linear-gradient(112deg', $css);
        $this->assertStringContainsString('linear-gradient(145deg', $css);
        $this->assertStringContainsString('var(--zazu-accent-soft) 72%', $css);
        $this->assertStringContainsString('var(--zazu-blue-soft) 76%', $css);
    }

    public function test_shared_panel_headers_use_the_same_soft_workspace_surface_language(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $pattern = '/\\.zazu-panel-head \\{[^}]*\\}/s';
        preg_match_all($pattern, $css, $matches);
        $this->assertNotEmpty($matches[0]);
        $effectiveRule = end($matches[0]);
        $this->assertStringContainsString('var(--zazu-surface-2)', $effectiveRule);
        $this->assertStringContainsString('border-bottom: 1px solid var(--zazu-border);', $effectiveRule);
    }

    public function test_workspace_search_matches_the_wide_desktop_reference_without_overriding_mobile_layout(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));

        $this->assertIsString($css);
        $this->assertIsString($mobileCss);
        $this->assertIsString($layout);
        $this->assertStringContainsString('@media (min-width: 1200px)', $css);
        $this->assertStringContainsString('flex: 1 1 clamp(420px, 48vw, 760px);', $css);
        $this->assertStringContainsString('max-width: 760px;', $css);
        $this->assertStringContainsString('width: 100%;', $mobileCss);
        $this->assertStringContainsString('placeholder="Search events, customers, quotes, inventory or job files…"', $layout);
    }

    public function test_dashboard_kpis_use_reference_aligned_icon_led_cards_on_desktop_and_mobile(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);
        $this->assertSame(5, substr_count($dashboard, 'class="zazu-dash-metric-icon"'));
        preg_match_all('/<span class="zazu-dash-metric-icon"[^>]*>\\s*<svg/s', $dashboard, $metricIcons);
        $this->assertCount(5, $metricIcons[0]);
        $this->assertStringContainsString('grid-template-columns: 48px minmax(0, 1fr);', $css);
        $this->assertStringContainsString('@media (max-width: 520px)', $css);
        $this->assertStringContainsString('grid-template-columns: 38px minmax(0, 1fr);', $css);
        $this->assertStringContainsString('number_format($metrics[', $dashboard);
    }

    public function test_dashboard_grid_matches_reference_scan_order_and_paired_panel_layout(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('/* DIRECTOR — dashboard hierarchy and card proportions.', $css);
        $this->assertStringContainsString('.zazu-dash-metrics {', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));', $css);
        $this->assertStringContainsString('.zazu-dash-run {', $css);
        $this->assertStringContainsString('grid-column: 1;', $css);
        $this->assertStringContainsString('.zazu-dash-commercial {', $css);
        $this->assertStringContainsString('grid-column: 2;', $css);
        $this->assertStringContainsString('.zazu-dash-focus {', $css);
        $this->assertStringContainsString('.zazu-dash-resources {', $css);
        $this->assertStringContainsString('@media (max-width: 900px)', $css);
        $this->assertStringContainsString('@media (max-width: 760px)', $css);
        $this->assertStringContainsString('@media (max-width: 380px)', $css);
    }
}

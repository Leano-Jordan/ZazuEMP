<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiProportionFoundationTest extends TestCase
{
    public function test_canonical_visual_tokens_match_the_coastal_blue_reference_family_and_disciplined_proportions(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--zazu-primary: #06749A;', $css);
        $this->assertStringContainsString('--zazu-primary-soft: #D8F1F6;', $css);
        $this->assertStringContainsString('--zazu-primary-deep: #086B8F;', $css);
        $this->assertStringContainsString('--zazu-page: #F2FAFD;', $css);
        $this->assertStringContainsString('--zazu-ui-control-height: 44px;', $css);
        $this->assertStringContainsString('--zazu-content-max: 1320px;', $css);
        $this->assertStringContainsString('--zazu-ui-radius: var(--zazu-radius-lg);', $css);
        $this->assertStringContainsString('--zazu-radius-lg: 10px;', $css);
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
        $this->assertStringContainsString('var(--zazu-surface-2)', $finalHeroRule);
        $this->assertStringNotContainsString('#FFFFFF', $finalHeroRule);

        foreach ($heroRules[1] as $heroRule) {
            $this->assertStringNotContainsString('#FFFFFF', $heroRule);
        }
    }

    public function test_dashboard_does_not_render_midnight_as_an_event_start_time(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString("{{ \$event->event_date ? 'DATE SET' : 'DATE TBC' }}", $dashboard);
        $this->assertStringContainsString("format('d M Y')", $dashboard);
        $this->assertStringNotContainsString("format('H:i')", $dashboard);
    }

    public function test_dashboard_only_surfaces_unpaid_invoice_metric_when_invoice_count_is_available(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString("\$workspaceTools['finance'] && \$experienceLevel !== 'basic'", $dashboard);
        $this->assertStringContainsString("@elseif(\$workspaceTools['quotes'])", $dashboard);
    }

    public function test_dashboard_does_not_present_static_network_connectivity_as_live_status(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringNotContainsString('<b>ONLINE</b>', $dashboard);
        $this->assertSame(2, substr_count($dashboard, '<b>EVENTS</b>'));
    }

    public function test_dashboard_priority_and_resource_readiness_use_a_balanced_lower_row(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.zazu-dash-focus {', $css);
        $this->assertStringContainsString('grid-column: 1;', $css);
        $this->assertStringContainsString('.zazu-dash-resources {', $css);
        $this->assertStringContainsString('grid-column: 2;', $css);
        $this->assertStringContainsString('grid-template-columns: 1fr;', $css);
    }

    public function test_dashboard_keeps_event_status_visible_on_mobile(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.zazu-dash-status {', $css);
        $this->assertStringContainsString('grid-column: 3;', $css);
        $this->assertStringContainsString('grid-row: 2;', $css);
        $this->assertStringContainsString('display: inline-flex;', $css);
    }


    public function test_dashboard_places_kpis_before_two_operational_queues_and_balances_the_lower_row(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        $metricsPosition = strpos($dashboard, '<aside class="zazu-dash-metrics">');
        $runPosition = strpos($dashboard, '<section class="zazu-dash-run');
        $commercialPosition = strpos($dashboard, '<section class="zazu-dash-commercial');
        $this->assertNotFalse($metricsPosition);
        $this->assertNotFalse($runPosition);
        $this->assertNotFalse($commercialPosition);
        $this->assertLessThan($runPosition, $metricsPosition);
        $this->assertLessThan($commercialPosition, $runPosition);

        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(auto-fit, minmax(min(100%, 190px), 1fr));', $css);
        $this->assertStringContainsString('.zazu-dash-metrics {', $css);
        $this->assertStringContainsString('grid-column: 1 / -1;', $css);
        $this->assertStringContainsString('.zazu-dash-commercial {', $css);
        $this->assertStringContainsString('grid-row: 2;', $css);
    }


    public function test_dashboard_uses_a_dedicated_quick_action_strip_between_metrics_and_work_queues(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        $heroStart = strpos($dashboard, '<section class="zazu-dash-hero">');
        $heroEnd = strpos($dashboard, '</section>', $heroStart);
        $metricsPosition = strpos($dashboard, '<aside class="zazu-dash-metrics">');
        $quickActionsPosition = strpos($dashboard, '<section class="zazu-dash-quick-actions');
        $runPosition = strpos($dashboard, '<section class="zazu-dash-run');
        $this->assertNotFalse($heroStart);
        $this->assertNotFalse($heroEnd);
        $this->assertNotFalse($metricsPosition);
        $this->assertNotFalse($quickActionsPosition);
        $this->assertNotFalse($runPosition);
        $this->assertFalse(strpos(substr($dashboard, $heroStart, $heroEnd - $heroStart), 'zazu-dash-actions'));
        $this->assertLessThan($quickActionsPosition, $metricsPosition);
        $this->assertLessThan($runPosition, $quickActionsPosition);
        $this->assertStringContainsString('grid-row: 2;', $css);
        $this->assertStringContainsString('flex-direction: column;', $css);
    }


    public function test_dashboard_cards_follow_compact_reference_proportions_and_balanced_resource_layout(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) minmax(300px, 34%);', $css);
        $this->assertStringContainsString('min-height: 210px;', $css);
        $this->assertStringContainsString('border-radius: 12px;', $css);
        $this->assertStringContainsString('font-size: 17px;', $css);
        $this->assertStringContainsString('min-height: 108px;', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('@media (max-width: 620px)', $css);
        $this->assertStringContainsString('.zazu-dash-resource-grid {', $css);
        $this->assertStringContainsString('grid-template-columns: 1fr;', $css);
    }

    public function test_authentication_submit_remains_intentionally_full_width(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.zazu-auth-submit {', $css);
        $this->assertStringContainsString('    width: 100%;', $css);
        preg_match('/\\.zazu-auth-submit \\{.*?\\}/s', $css, $authSubmit);
        $this->assertNotEmpty($authSubmit);
        $this->assertStringNotContainsString('max-width:', $authSubmit[0]);
    }

}

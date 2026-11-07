<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiDashboardCompositionTest extends TestCase
{
    public function test_dashboard_does_not_present_static_network_connectivity_or_fake_workspace_health(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringNotContainsString('<b>ONLINE</b>', $dashboard);
        $this->assertStringNotContainsString('Workspace state', $dashboard);
        $this->assertStringNotContainsString('<b>EVENTS</b>', $dashboard);
    }

    public function test_dashboard_quote_pipeline_uses_real_quote_lifecycle_status_counts(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/DashboardController.php'));
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($controller);
        $this->assertIsString($dashboard);
        $this->assertIsString($css);
        $this->assertStringContainsString("->groupBy('status')", $controller);
        $this->assertStringContainsString("->pluck('total', 'status')", $controller);
        $this->assertStringContainsString("$"."quoteStatusCounts", $controller);
        $this->assertStringContainsString("['declined']", $dashboard);
        $this->assertStringContainsString("['expired']", $dashboard);
        $this->assertStringContainsString('Quote status breakdown', $dashboard);
        $this->assertStringContainsString('Approved', $dashboard);
        $this->assertStringContainsString('Declined / expired', $dashboard);
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $css);
    }

    public function test_dashboard_workspace_health_uses_four_compact_live_business_metrics(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);
        $this->assertStringContainsString('Workspace health', $dashboard);
        $this->assertStringContainsString('Workspace health', $dashboard);
        $this->assertStringContainsString('Live business records at a glance', $dashboard);
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('@media (max-width: 380px)', $css);
        $this->assertStringNotContainsString('System Operational', $dashboard);
        $this->assertStringNotContainsString('Finance Connected', $dashboard);
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

    public function test_dashboard_mobile_event_rows_follow_the_current_three_item_markup(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);
        $this->assertStringContainsString('class="zazu-dash-event-date"', $dashboard);
        $this->assertStringContainsString('class="zazu-dash-event-copy"', $dashboard);
        $this->assertStringContainsString('class="zazu-dash-event-trailing"', $dashboard);
        $this->assertStringContainsString('.zazu-dash-event-trailing .zazu-dash-status {', $css);
        $this->assertStringContainsString('display: inline-flex;', $css);
        $this->assertStringNotContainsString(
            '/* Mobile run-sheet: keep operational status visible without squeezing event details. */',
            $css
        );
        $this->assertStringNotContainsString(
            '.zazu-dash-event-time,\n    .zazu-dash-event-line {\n        grid-row: 1 / span 2;',
            $css
        );
        $this->assertStringNotContainsString(
            '.zazu-dash-status {\n        display: inline-flex;\n        grid-column: 3;',
            $css
        );
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
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr));', $css);
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
}

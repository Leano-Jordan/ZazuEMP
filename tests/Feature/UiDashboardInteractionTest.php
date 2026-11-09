<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiDashboardInteractionTest extends TestCase
{
    public function test_dashboard_workspace_health_only_renders_when_it_has_enabled_resource_metrics(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString("$"."experienceLevel !== 'basic'", $dashboard);

        foreach ([
            "$"."workspaceTools['purchasing']",
            "$"."workspaceTools['inventory']",
            "$"."workspaceTools['assets']",
            "$"."workspaceTools['finance']",
        ] as $resourceTool) {
            $this->assertStringContainsString($resourceTool, $dashboard);
        }

        $healthGate = strpos($dashboard, "$"."experienceLevel !== 'basic'");
        $healthPanel = strpos($dashboard, '<section class="zazu-dash-resources zazu-dash-surface">');
        $this->assertNotFalse($healthGate);
        $this->assertNotFalse($healthPanel);
        $this->assertLessThan($healthPanel, $healthGate);
        $this->assertStringContainsString('Workspace health', substr($dashboard, $healthPanel, 700));
    }

    public function test_dashboard_visual_finish_preserves_coastal_hero_kpi_and_panel_hierarchy(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);

        foreach ([
            '.zazu-dash-hero {',
            'grid-template-columns: minmax(0, 1fr) minmax(250px, .34fr);',
            '.zazu-dash-hero-side {',
            'background: color-mix(in srgb, var(--zazu-blue-soft) 62%, var(--zazu-card-bg));',
            '.zazu-dash-metric {',
            'grid-template-columns: 42px minmax(0, 1fr);',
            '.zazu-dash-surface {',
            'box-shadow: 0 5px 20px rgba(22, 66, 96, .055);',
            '@media (max-width: 900px)',
        ] as $expected) {
            $this->assertStringContainsString($expected, $css);
        }
    }

    public function test_dashboard_hides_event_and_quote_details_without_their_view_permissions(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);

        $eventsGate = strpos($dashboard, "@if(\$workspaceTools['work'] || \$workspaceTools['calendar'])");
        $eventsPanel = strpos($dashboard, '<section class="zazu-dash-run zazu-dash-surface">');
        $quotesGate = strpos($dashboard, "@if(\$workspaceTools['quotes'])");
        $quotesPanel = strpos($dashboard, '<section class="zazu-dash-commercial zazu-dash-surface">');

        $this->assertNotFalse($eventsGate);
        $this->assertNotFalse($eventsPanel);
        $this->assertNotFalse($quotesGate);
        $this->assertNotFalse($quotesPanel);
        $this->assertLessThan($eventsPanel, $eventsGate);
        $this->assertLessThan($quotesPanel, $quotesGate);
    }

    public function test_dashboard_priority_clear_state_ignores_metrics_for_hidden_resources(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);

        foreach ([
            "(!\$workspaceTools['purchasing'] || \$experienceLevel === 'basic' || \$metrics['open_purchase_orders'] === 0)",
            "(!\$workspaceTools['inventory'] || \$experienceLevel === 'basic' || \$metrics['low_stock'] === 0)",
            "(!\$workspaceTools['finance'] || \$experienceLevel === 'basic' || \$metrics['unpaid_invoices'] === 0)",
        ] as $permissionAwareMetric) {
            $this->assertStringContainsString($permissionAwareMetric, $dashboard);
        }
    }

    public function test_dashboard_lower_panels_use_compact_surfaces_and_responsive_resource_tiles(): void
    {
        $css = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('Director iteration 01 — lower dashboard panels.', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr)) !important;', $css);
        $this->assertStringContainsString('min-height: 62px !important;', $css);
        $this->assertStringContainsString('html[data-theme="dark"] body.zazu-dashboard-page .zazu-dash-focus-item', $css);
        $this->assertStringContainsString('@media (max-width: 380px)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

    public function test_dashboard_quick_actions_use_quiet_surfaces_with_theme_and_mobile_parity(): void
    {
        $css = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('Director iteration 02 — quiet the primary action row.', $css);
        $this->assertStringContainsString('Owner visual correction — stronger surface separation, borderless quick actions.', $css);
        $this->assertStringContainsString('border: 0 !important;', $css);
        $this->assertStringContainsString('background: #E7EDF3 !important;', $css);
        $this->assertStringContainsString('border: 1px solid #B8C7D5 !important;', $css);
        $this->assertStringContainsString('outline: 2px solid #163F67 !important;', $css);
        $this->assertStringContainsString('html[data-theme="dark"] body.zazu-dashboard-page .zazu-dash-quick-actions .zazu-btn:not(.zazu-btn-cta)', $css);
        $this->assertStringContainsString('@media (max-width: 420px)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

    public function test_dashboard_top_half_uses_a_single_overview_composition_before_operational_panels(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        $overview = strpos($dashboard, '<section class="zazu-dash-overview"');
        $metrics = strpos($dashboard, '<aside class="zazu-dash-metrics">');
        $actions = strpos($dashboard, '<section class="zazu-dash-quick-actions"');
        $panels = strpos($dashboard, '<section class="zazu-dash-grid" aria-label="Operational dashboard panels">');

        $this->assertNotFalse($overview);
        $this->assertNotFalse($metrics);
        $this->assertNotFalse($actions);
        $this->assertNotFalse($panels);
        $this->assertLessThan($metrics, $overview);
        $this->assertLessThan($actions, $metrics);
        $this->assertLessThan($panels, $actions);

        $this->assertStringContainsString('.zazu-dash-overview {', $css);
        $this->assertStringContainsString('.zazu-dash-overview .zazu-dash-metrics {', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr)) !important;', $css);
        $this->assertStringContainsString('html[data-theme="dark"] body.zazu-dashboard-page .zazu-dash-hero', $css);
        $this->assertStringContainsString('@media (max-width: 760px)', $css);
    }

    public function test_dashboard_operational_panels_share_balanced_rows_and_internal_spacing(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        foreach ([
            'zazu-dash-run zazu-dash-surface',
            'zazu-dash-commercial zazu-dash-surface',
            'zazu-dash-focus zazu-dash-surface',
            'zazu-dash-resources zazu-dash-surface',
        ] as $panel) {
            $this->assertStringContainsString($panel, $dashboard);
        }

        $this->assertStringContainsString('Director iteration 04 — balance the operational dashboard rows.', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr) !important;', $css);
        $this->assertStringContainsString('.zazu-dash-commercial .zazu-dash-quote-stages', $css);
        $this->assertStringContainsString('.zazu-dash-focus .zazu-dash-focus-grid', $css);
        $this->assertStringContainsString('.zazu-dash-resources .zazu-dash-resource-grid', $css);
        $this->assertStringContainsString('html[data-theme="dark"] body.zazu-dashboard-page .zazu-dash-run .zazu-dash-event', $css);
        $this->assertStringContainsString('@media (max-width: 760px)', $css);
    }

    public function test_dashboard_panel_contents_have_a_clear_inset_from_the_panel_header(): void
    {
        $css = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('padding: 12px 14px 12px !important;', $css);
        $this->assertStringContainsString('padding: 12px 14px 11px !important;', $css);
        $this->assertStringNotContainsString('padding: 3px 14px 12px !important;', $css);
        $this->assertStringNotContainsString('padding: 2px 14px 11px !important;', $css);
    }

}

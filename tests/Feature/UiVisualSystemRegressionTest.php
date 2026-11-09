<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiVisualSystemRegressionTest extends TestCase
{
    public function test_dashboard_quick_actions_use_reference_led_icon_and_supporting_copy(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        foreach ([
            'zazu-dash-action-icon',
            'zazu-dash-action-copy',
            'Start a new job',
            'Turn an enquiry into a proposal',
            'People and organisations',
            'View your schedule',
        ] as $expected) {
            $this->assertStringContainsString($expected, $dashboard);
        }

        $this->assertStringContainsString('.zazu-dash-action-icon {', $css);
        $this->assertStringContainsString('.zazu-dash-action-copy strong {', $css);
        $this->assertStringContainsString('.zazu-dash-action-copy small {', $css);
        $this->assertStringContainsString('grid-template-columns: 34px minmax(0, 1fr);', $css);
        $this->assertStringContainsString('aria-hidden="true"><svg', $dashboard);
    }

    public function test_dashboard_sparse_metrics_and_permission_filtered_actions_keep_balanced_columns(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.zazu-dash-metrics:has(> .zazu-dash-metric:only-child)', $css);
        $this->assertStringContainsString('.zazu-dash-metrics:has(> .zazu-dash-metric:nth-child(2):last-child)', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(190px, 360px));', $css);
        $this->assertStringContainsString('repeat(auto-fit, minmax(min(100%, 230px), 1fr))', $css);
    }

    public function test_dashboard_event_and_quote_panels_follow_reference_hierarchy(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        foreach ([
            'zazu-dash-panel-icon',
            'zazu-dash-panel-heading',
            'Upcoming events',
            'zazu-dash-event-date',
            'zazu-dash-event-trailing',
            'Quote pipeline',
            'Declined / expired',
            '($quoteStatusCounts[\'accepted\'] ?? 0)',
        ] as $expected) {
            $this->assertStringContainsString($expected, $dashboard);
        }

        $this->assertStringContainsString('.zazu-dash-panel-head {', $css);
        $this->assertStringContainsString('.zazu-dash-event-date {', $css);
        $this->assertStringContainsString('.zazu-dash-event-trailing {', $css);
        $this->assertStringContainsString('.zazu-dash-commercial .zazu-dash-quote-stage > span::before {', $css);
        $this->assertStringContainsString('@media (max-width: 380px)', $css);
        $this->assertStringNotContainsString('zazu-dash-event-line', $dashboard);
    }

    public function test_dashboard_priority_and_workspace_health_panels_use_truthful_resource_states(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        foreach ([
            'Priority now',
            'Workspace health',
            'zazu-dash-health-item',
            'Replenishment needed',
            'No items need replenishment',
            'Payment follow-up required',
            'No outstanding invoices',
            '$metrics[\'available_assets\'] > 0',
        ] as $expected) {
            $this->assertStringContainsString($expected, $dashboard);
        }

        $this->assertStringContainsString('.zazu-dash-health-item {', $css);
        $this->assertStringContainsString('.zazu-dash-health-label i.is-warning', $css);
        $this->assertStringContainsString('.zazu-dash-health-label i.is-clear', $css);
        $this->assertStringContainsString('@media (max-width: 620px)', $css);
    }

    public function test_dashboard_panels_keep_compact_internal_density_and_aligned_rows(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('min-height: 76px;', $css);
        $this->assertStringContainsString('min-height: 58px;', $css);
        $this->assertStringContainsString('min-height: 82px;', $css);
        $this->assertStringContainsString('padding: 0 16px 10px;', $css);
        $this->assertStringContainsString('min-height: 98px !important;', $css);
        $this->assertStringContainsString('gap: 7px;', $css);
    }

    public function test_dashboard_panel_headers_keep_titles_without_redundant_subtitles(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        foreach ([
            'Upcoming events',
            'Quote pipeline',
            'Priority now',
            'Workspace health',
        ] as $title) {
            $this->assertStringContainsString('<h3>' . $title . '</h3>', $dashboard);
        }

        foreach ([
            'Real events from your workspace, ordered by date.',
            'Turn outstanding commercial work into the next action.',
            'What needs your attention today.',
            'Key operational resources at a glance.',
        ] as $redundantCopy) {
            $this->assertStringNotContainsString($redundantCopy, $dashboard);
        }
    }

    public function test_dashboard_priority_empty_state_does_not_claim_schedule_is_checked(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString('No setup, quote or resource action needs attention right now.', $dashboard);
        $this->assertStringNotContainsString('no setup, schedule, quote or resource action', $dashboard);
    }

    public function test_dashboard_event_status_remains_visible_in_compact_mobile_rows(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media (max-width: 620px)', $css);
        $this->assertStringContainsString('.zazu-dash-event-trailing .zazu-dash-status {', $css);
        $this->assertStringContainsString('display: inline-flex;', $css);
        $this->assertStringContainsString('text-overflow: ellipsis;', $css);
    }

    public function test_disclosure_content_is_inset_below_its_divider_on_desktop_and_mobile(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString(".zazu-disclosure-body {\npadding:12px 16px 16px;", $css);
        $this->assertStringContainsString(".zazu-disclosure-body {\npadding:10px 13px 13px", $css);
        $this->assertStringNotContainsString('.zazu-disclosure-body {\npadding:0 16px 16px;', $css);
        $this->assertStringNotContainsString('.zazu-disclosure-body {\npadding:0 13px 13px', $css);
    }


    public function test_authenticated_modules_share_the_dashboard_shell_and_surface_palette(): void
    {
        $css = file_get_contents(resource_path('css/zazu-product-authority.css'));
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));

        $this->assertIsString($css);
        $this->assertIsString($layout);
        $this->assertStringContainsString('resources/css/zazu-product-authority.css', $layout);
        $this->assertStringContainsString('--zazu-page: #DCE5ED;', $css);
        $this->assertStringContainsString('--zazu-sidebar: #102541;', $css);
        $this->assertStringContainsString('--zazu-surface: #FFFFFF;', $css);
        $this->assertStringContainsString('--zazu-page: #0B1726;', $css);
        $this->assertStringContainsString('--zazu-sidebar: #091827;', $css);
        $this->assertStringContainsString('background-image: none !important;', $css);
        $this->assertStringContainsString('.zazu-dash-commercial .zazu-dash-quote-stages', $css);
        $this->assertStringContainsString('padding: 12px 18px 14px !important;', $css);
    }


}

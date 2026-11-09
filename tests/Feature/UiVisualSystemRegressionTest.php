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
        $dashboardCss = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));
        $visualSweepCss = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertIsString($layout);
        $this->assertIsString($dashboardCss);
        $this->assertIsString($visualSweepCss);
        $this->assertStringContainsString('resources/css/zazu-product-authority.css', $layout);
        $quoteCreate = file_get_contents(resource_path('views/quotes/create.blade.php'));
        $this->assertIsString($quoteCreate);
        $this->assertStringContainsString('class="zazu-list zazu-quote-line-editor"', $quoteCreate);
        $this->assertStringNotContainsString('class="zazu-card zazu-list zazu-quote-line-editor"', $quoteCreate);
        $this->assertStringContainsString('.zazu-quote-line-editor .zazu-list-item', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) minmax(140px, 190px);', $css);
        $this->assertStringContainsString('--zazu-page: #DCE5ED;', $css);
        $this->assertStringContainsString('--zazu-sidebar: #102541;', $css);
        $this->assertStringContainsString('--zazu-surface: #FFFFFF;', $css);
        $this->assertStringContainsString('--zazu-page: #0B1726;', $css);
        $this->assertStringContainsString('--zazu-sidebar: #091827;', $css);
        $this->assertStringContainsString('background-image: none !important;', $css);
        $this->assertStringContainsString('.zazu-dash-commercial .zazu-dash-quote-stages', $css);
        $this->assertStringContainsString('padding: 12px 18px 14px !important;', $css);
        $this->assertStringNotContainsString('border: 2px solid #B9C9D7 !important;', $dashboardCss);
        $this->assertStringNotContainsString('border-right: 2px solid #B5C5D3 !important;', $dashboardCss);
        $this->assertStringNotContainsString('border-bottom-width: 2px;', $visualSweepCss);
        $this->assertStringContainsString('border-bottom-width: 1px;', $visualSweepCss);

             foreach ([
            '--zazu-tone-green: #E2F3E8;',
            '--zazu-tone-amber: #FFF0D2;',
            '--zazu-tone-teal: #DDF3EF;',
            '--zazu-tone-violet: #EEE8FA;',
            '--zazu-tone-coral: #FCE9E4;',
            'border-width: 1px !important;',
            'body[data-zazu-route^="customers."] .zazu-contact-card',
            'body[data-zazu-route^="customers."] .zazu-detail-value-box',
            '.zazu-work-progress-step.current',
            '.zazu-work-progress-step.complete',
            'var(--zazu-tone-ink-green)',
            '.zazu-work-progress-step:not(.current):not(.complete)',
            '.zazu-form-section-head',
            'body[data-zazu-route^="finance."] .zazu-form-section-head',
            'body[data-zazu-route^="inventory."] .zazu-form-section-head',
            'body[data-zazu-route^="work.quotes."] .zazu-form-section-head',
            'body[data-zazu-route^="work."]:not([data-zazu-route^="work.quotes."]) .zazu-form-section-head',
            'body[data-zazu-route^="finance."] .zazu-form-section',
            'margin: 0 0 14px !important;',
            'border-radius: 7px !important;',
            'body[data-zazu-route^="finance."] .zazu-card',
            'body[data-zazu-route^="inventory."] .zazu-card',
            'body[data-zazu-route^="suppliers."] .zazu-card',
            'body[data-zazu-route^="customers."] .zazu-card',
            'body[data-zazu-route^="quotes."] .zazu-card',
            'body[data-zazu-route^="reports."] .zazu-card',
            '.zazu-quote-register-command + .zazu-card .zazu-card-header',
            'body.zazu-dashboard-page .zazu-sidebar',
            'body.zazu-dashboard-page .zazu-topbar',
            'body.zazu-dashboard-page .zazu-dash-hero',
            'body.zazu-dashboard-page .zazu-dash-surface-head',
            '.zazu-calendar-holiday',
            '.zazu-calendar-agenda-holiday',
            '.zazu-compact-editor-card .zazu-card-header',
            '.zazu-dash-surface-head',
            '.zazu-dash-hero-side',
            '.zazu-compact-editor-card .zazu-card-header',
            '.zazu-start-intent',
            'border-left-width: 1px !important;',
            '.zazu-metric-card:nth-child(4n + 2)',
            '.zazu-metric-card:nth-child(4n + 3)',
            '.zazu-metric-card:nth-child(4n + 4)',
            '.zazu-dash-quote-stage:nth-child(4)',
            'background: var(--zazu-tone-green) !important;',
            '--zazu-tone-blue: #D3ECFF;',
            '--zazu-tone-green: #D1F2D9;',
            '--zazu-tone-amber: #FFE4A3;',
            '--zazu-tone-teal: #C4F0E6;',
            '--zazu-tone-violet: #E2D5FF;',
            '--zazu-tone-coral: #FFD5CA;',
            'color: var(--zazu-sidebar-ink) !important;',
            'background: #34536D !important;',
            '.zazu-ops-actions .zazu-btn',
            'outline: 2px solid #4A91B8 !important;',
            'body.zazu-dashboard-page .zazu-dash-resource-grid > :nth-child(4)',
            'body.zazu-dashboard-page .zazu-dash-quote-stage:nth-child(3)',
            ':root[data-theme="dark"] .zazu-dash-quote-stage:nth-child(4)',
            '.zazu-finance-directory .zazu-card-header',
            '.zazu-inventory-directory .zazu-card-header',
            '.zazu-suppliers-page .zazu-card-header',
            '.zazu-customer-directory .zazu-card-header',
            'top: 50% !important;',
            'right: max(6px, env(safe-area-inset-right)) !important;',
            'z-index: 35 !important;',
            'bottom: max(10px, env(safe-area-inset-bottom)) !important;',
        ] as $expected) {
            $this->assertStringContainsString($expected, $css);
        }
    }


}

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
        $this->assertStringContainsString('.zazu-dash-capability-art {', $css);
        $this->assertStringContainsString('@media (max-width: 620px)', $css);
    }

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
        $this->assertStringContainsString('Accepted', $dashboard);
        $this->assertStringContainsString('Declined or expired', $dashboard);
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
        $this->assertStringContainsString('Operational readiness', $dashboard);
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


    public function test_sidebar_navigation_and_workspace_base_rules_have_one_canonical_definition(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $responsiveCss = file_get_contents(resource_path('css/zazu-responsive-theme.css'));
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));

        $this->assertIsString($appCss);
        $this->assertIsString($responsiveCss);
        $this->assertIsString($mobileCss);

        $canonicalSelectors = [
            '.zazu-nav-stack',
            '.zazu-nav-group',
            '.zazu-nav-label',
            '.zazu-nav-link',
            '.zazu-nav-icon',
            '.zazu-sidebar-workspace',
            '.zazu-sidebar-workspace-label',
            '.zazu-sidebar-workspace-name',
            '.zazu-sidebar-workspace-meta',
            '.zazu-sidebar-workspace-dot',
            '.zazu-nav-link:hover',
            '.zazu-nav-link.active',
            '.zazu-nav-link.active::after',
            '.zazu-nav-link:hover .zazu-nav-icon',
            '.zazu-nav-link.active .zazu-nav-icon',
            '.zazu-nav-priority',
            '.zazu-footer-card',
            '.zazu-footer-title',
            '.zazu-footer-copy',
            '.zazu-brand-link',
            '.zazu-brand-logo',
            '.zazu-brand-lockup strong',
            '.zazu-brand-lockup span',
            '.zazu-nav-area-title',
            '.zazu-nav-flyout-heading',
            '.zazu-nav-flyout-links',
            '.zazu-nav-area.is-open .zazu-nav-area-trigger',
            '.zazu-nav-area.is-open > .zazu-nav-area-trigger',
            '.zazu-nav-area.is-active > .zazu-nav-area-trigger',
        ];

        foreach ($canonicalSelectors as $selector) {
            $pattern = '/^[ \\t]*' . preg_quote($selector, '/') . '\\s*\\{/m';
            $this->assertSame(
                1,
                preg_match_all($pattern, $appCss),
                $selector . ' should have one canonical base rule.'
            );
        }

        // Keep state rules and responsive ownership separate from base geometry.
        $this->assertStringContainsString('.zazu-nav-link:hover {', $appCss);
        $this->assertStringContainsString('.zazu-nav-link.active {', $appCss);
        $this->assertStringContainsString('.zazu-nav-flyout .zazu-nav-link.active {', $appCss);
        $this->assertStringContainsString('.zazu-brand-logo,', $appCss);
        $this->assertStringContainsString('.zazu-brand-mark {', $appCss);
        $this->assertStringContainsString(':root:not([data-theme="dark"]) .zazu-nav-link.active {', $appCss);
        $this->assertStringContainsString('.zazu-nav-flyout .zazu-nav-link.active {', $appCss);
        $this->assertStringContainsString('@media (max-width: 850px)', $appCss);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $appCss);
        $this->assertStringContainsString('.zazu-nav-area-chevron {', $appCss);
        $this->assertStringContainsString('overflow-y: auto;', $responsiveCss);
        $this->assertStringContainsString('body.zazu-mobile-menu-open .zazu-sidebar {', $mobileCss);
        $this->assertStringContainsString('min-height: 44px;', $mobileCss);
    }


    public function test_topbar_title_and_theme_toggle_have_one_canonical_base_with_theme_and_viewport_overrides(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $extensionCss = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));
        $responsiveCss = file_get_contents(resource_path('css/zazu-responsive-theme.css'));
        $mobileCss = file_get_contents(resource_path('css/zazu-mobile-refinement.css'));

        $this->assertIsString($appCss);
        $this->assertIsString($extensionCss);
        $this->assertIsString($responsiveCss);
        $this->assertIsString($mobileCss);

        foreach ([
            '.zazu-topbar',
            '.zazu-topbar-inner',
            '.zazu-page-title',
            '.zazu-eyebrow',
            '.zazu-theme-toggle',
        ] as $selector) {
            $pattern = '/^' . preg_quote($selector, '/') . '\\s*\\{/m';
            $this->assertSame(
                1,
                preg_match_all($pattern, $appCss),
                $selector . ' should have one canonical desktop base rule.'
            );
        }

        // Shared eyebrow typography belongs to app.css, not the component extension layer.
        $this->assertSame(0, preg_match('/^\\.zazu-eyebrow\\s*\\{/m', $extensionCss));
        $this->assertSame(0, preg_match('/\\.zazu-caption,\\s*\\.zazu-eyebrow,/', $extensionCss));

        // Theme values and wallpaper treatment remain token/state driven.
        $this->assertStringContainsString('--zazu-sidebar: #E4F6FC;', $appCss);
        $this->assertStringContainsString('--zazu-sidebar: #061826;', $appCss);
        $this->assertStringContainsString('--zazu-sidebar-ink: #F3F7FB;', $appCss);
        $this->assertStringContainsString('body.zazu-has-wallpaper .zazu-topbar', $appCss);
        $this->assertStringContainsString('.zazu-theme-toggle:hover {', $appCss);
        $this->assertStringContainsString('.zazu-theme-toggle:active {', $appCss);
        $this->assertStringContainsString('.zazu-theme-toggle:focus-visible', $appCss);

        // Responsive shell ownership stays in its dedicated stylesheets.
        $this->assertStringContainsString('@media (min-width: 821px)', $responsiveCss);
        $this->assertStringContainsString('.zazu-topbar-actions {', $responsiveCss);
        $this->assertStringContainsString('@media (max-width: 850px)', $mobileCss);
        $this->assertStringContainsString('grid-template-areas:', $mobileCss);
        $this->assertStringContainsString('width: 40px;', $mobileCss);
        $this->assertStringContainsString('font-size: 17px;', $mobileCss);
        $this->assertStringContainsString('height: 40px;', $mobileCss);
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

    public function test_dashboard_priority_queue_does_not_repeat_setup_or_upcoming_events_already_shown_above(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $focusStart = strpos($dashboard, '<section class="zazu-dash-focus');
        $focusEnd = strpos($dashboard, '</section>', $focusStart);
        $this->assertNotFalse($focusStart);
        $this->assertNotFalse($focusEnd);

        $priority = substr($dashboard, $focusStart, $focusEnd - $focusStart);
        $this->assertStringNotContainsString('Finish workspace setup', $priority);
        $this->assertStringNotContainsString('Next scheduled work:', $priority);
        $this->assertStringContainsString('draft quote', $priority);
        $this->assertStringContainsString('need replenishment', $priority);
    }


    public function test_dashboard_run_sheet_is_limited_to_five_compact_upcoming_events(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/DashboardController.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($controller);
        $this->assertIsString($css);
        $this->assertStringContainsString("->limit(5)", $controller);
        $this->assertStringContainsString(".zazu-dash-event {\n    min-height: 64px;", $css);
        $this->assertStringContainsString("All work", file_get_contents(resource_path('views/dashboard.blade.php')));
    }


    public function test_dashboard_does_not_repeat_workspace_navigation_below_quick_actions(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);
        $this->assertStringNotContainsString('zazu-dash-commandbar', $dashboard);
        $this->assertStringNotContainsString('Workspace surfaces', $dashboard);
        $this->assertStringNotContainsString('.zazu-dash-commandbar', $css);
        $this->assertStringContainsString('aria-label="Quick actions"', $dashboard);
    }


    public function test_dashboard_supporting_copy_stays_compact_and_non_repetitive(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertStringNotContainsString('Start the next piece of work.', $dashboard);
        $this->assertStringNotContainsString('Real events from the workspace, ordered by execution date.', $dashboard);
        $this->assertStringNotContainsString('Turn outstanding commercial work into the next deliberate action.', $dashboard);
        $this->assertStringNotContainsString('A short action queue based on the records already visible', $dashboard);
        $this->assertStringNotContainsString('Finance remains authoritative in its own workspace.', $dashboard);
        $this->assertStringContainsString('aria-label="Quick actions"', $dashboard);
        $this->assertStringContainsString('Quote pipeline', $dashboard);
        $this->assertStringContainsString('Operational readiness', $dashboard);
    }


    public function test_dashboard_kpis_keep_only_the_label_and_live_value(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($dashboard);
        $this->assertIsString($css);

        foreach ([
            'Open operational records',
            'Commercial records awaiting action',
            'People and organisations',
            'Scheduled work ahead',
        ] as $redundantCaption) {
            $this->assertStringNotContainsString($redundantCaption, $dashboard);
        }

        $this->assertStringContainsString('Active events', $dashboard);
        $this->assertStringContainsString('Draft quotes', $dashboard);
        $this->assertStringContainsString('Customers', $dashboard);
        $this->assertStringContainsString('Next 14 days', $dashboard);
        $this->assertStringNotContainsString('.zazu-dash-metric>small', $css);
    }


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
        $this->assertStringContainsString('--zazu-sidebar: #B9D5EF;', $css);
        $this->assertStringContainsString('--zazu-sidebar-ink: #173D66;', $css);
        $this->assertStringContainsString('--zazu-sidebar-active: #1768C4;', $css);
        $this->assertStringContainsString('background: color-mix(in srgb, var(--zazu-surface) 94%, var(--zazu-page));', $css);
        $this->assertStringContainsString('color: var(--zazu-ink);', $css);
        $this->assertStringContainsString(':root[data-theme="dark"] {', $css);
        $this->assertStringContainsString('--zazu-sidebar: #061826;', $css);
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


}

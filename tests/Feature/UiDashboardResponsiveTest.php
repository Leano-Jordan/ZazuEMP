<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiDashboardResponsiveTest extends TestCase
{
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
        $this->assertStringContainsString('--zazu-sidebar: #8ED8F8;', $appCss);
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
        $this->assertStringContainsString('View all events', file_get_contents(resource_path('views/dashboard.blade.php')));
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
        $this->assertStringContainsString('Workspace health', $dashboard);
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
}

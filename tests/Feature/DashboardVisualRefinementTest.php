<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardVisualRefinementTest extends TestCase
{
    public function test_dashboard_create_event_is_brighter_and_upcoming_events_use_a_neutral_surface(): void
    {
        $dashboardCss = file_get_contents(resource_path('css/zazu-dashboard-authority.css'));
        $productCss = file_get_contents(resource_path('css/zazu-product-authority.css'));

        $this->assertIsString($dashboardCss);
        $this->assertIsString($productCss);
        $this->assertStringContainsString('background: var(--zazu-primary) !important;', $dashboardCss);
        $this->assertStringContainsString('color: #0B253B !important;', $dashboardCss);
        $this->assertStringContainsString('background: var(--zazu-primary-light) !important;', $dashboardCss);
        $this->assertStringContainsString('color: var(--zazu-primary-ink) !important;', $dashboardCss);
        $this->assertStringContainsString(
            'body.zazu-dashboard-page .zazu-dash-event { background: var(--zazu-surface) !important; }',
            $productCss
        );
        $this->assertStringNotContainsString(
            'body.zazu-dashboard-page .zazu-dash-event { background: var(--zazu-tone-blue) !important; }',
            $productCss
        );
    }
}

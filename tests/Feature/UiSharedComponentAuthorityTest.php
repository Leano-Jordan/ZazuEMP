<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiSharedComponentAuthorityTest extends TestCase
{
        public function test_authenticated_modules_keep_shared_component_authority_and_workspace_primitives(): void
        {
            $css = file_get_contents(resource_path('css/zazu-product-authority.css'));
            $this->assertIsString($css);
            foreach ([
                'border-width: 1px !important;',
                'body[data-zazu-route^="customers."] .zazu-contact-card',
                'body[data-zazu-route^="customers."] .zazu-detail-value-box',
                '.zazu-work-progress-step.current',
                '.zazu-work-progress-step.complete',
                'var(--zazu-tone-ink-green)',
                '.zazu-work-progress-step:not(.current):not(.complete)',
                '.zazu-card-header,',
                '.zazu-panel-head,',
                '.zazu-form-section-head,',
                'background: var(--zazu-surface-3) !important;',
                'border-bottom: 1px solid var(--zazu-border-subtle) !important;',
                'body[data-zazu-route^="finance."] .zazu-card',
                'body[data-zazu-route^="inventory."] .zazu-card',
                'body[data-zazu-route^="suppliers."] .zazu-card',
                'body[data-zazu-route^="customers."] .zazu-card',
                'body[data-zazu-route^="quotes."] .zazu-card',
                'body[data-zazu-route^="reports."] .zazu-card',
                'body.zazu-dashboard-page .zazu-sidebar',
                'body.zazu-dashboard-page .zazu-topbar',
                'body.zazu-dashboard-page .zazu-dash-hero',
                'body.zazu-dashboard-page .zazu-dash-surface-head',
                '.zazu-calendar-holiday',
                '.zazu-calendar-agenda-holiday',
                '.zazu-compact-editor-card .zazu-card-header',
                '.zazu-dash-surface-head',
                '.zazu-dash-hero-side',
                '.zazu-start-intent',
                'border-left-width: 1px !important;',
                '.zazu-metric-card:nth-child(4n + 2)',
                '.zazu-metric-card:nth-child(4n + 3)',
                '.zazu-metric-card:nth-child(4n + 4)',
                '.zazu-dash-quote-stage:nth-child(4)',
                'background: var(--zazu-tone-green) !important;',
                '--zazu-tone-amber: #FFE4A3;',
                '--zazu-tone-teal: #C4F0E6;',
                '--zazu-tone-violet: #E2D5FF;',
                '--zazu-tone-coral: #FFD5CA;',
                'color: var(--zazu-sidebar-ink) !important;',
                '.zazu-btn {',
                '.zazu-btn-ghost {',
                'body.zazu-dashboard-page .zazu-dash-resource-grid > :nth-child(4n + 4)',
                'body.zazu-dashboard-page .zazu-dash-quote-stage:nth-child(3)',
            ] as $expected) {
                $this->assertStringContainsString($expected, $css);
            }
        }
}

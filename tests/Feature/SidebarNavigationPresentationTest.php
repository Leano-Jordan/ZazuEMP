<?php

namespace Tests\Feature;

use Tests\TestCase;

class SidebarNavigationPresentationTest extends TestCase
{
    public function test_sidebar_merges_workspace_identity_into_the_account_control(): void
    {
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $visualCss = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($layout);
        $this->assertIsString($visualCss);
        $this->assertStringContainsString('class="zazu-user-identity zazu-footer-workspace"', $layout);
        $this->assertStringContainsString('class="zazu-user-kicker zazu-footer-workspace-label">WORKSPACE</span>', $layout);
        $this->assertStringNotContainsString('<div class="zazu-footer-workspace">', $layout);
        $this->assertStringContainsString('.zazu-sidebar .zazu-workspace-profile {', $visualCss);
        $this->assertStringContainsString('.zazu-sidebar .zazu-user-trigger {', $visualCss);
        $this->assertStringContainsString('@media (max-width: 850px)', $visualCss);
    }

    public function test_navigation_exposes_a_persistent_collapsed_rail_and_advanced_search(): void
    {
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $styles = file_get_contents(resource_path('css/app.css'));
        $scripts = file_get_contents(resource_path('js/app.js'));
        $search = file_get_contents(resource_path('views/search/index.blade.php'));

        $this->assertIsString($layout);
        $this->assertIsString($styles);
        $this->assertIsString($scripts);
        $this->assertIsString($search);
        $this->assertStringContainsString('data-sidebar-collapse-toggle', $layout);
        $this->assertStringContainsString('zazu-sidebar-collapsed', $styles);
        $this->assertStringContainsString('setupZazuSidebarCollapse', $scripts);
        $this->assertStringContainsString('Advanced Search', $layout);
        $this->assertStringContainsString('Advanced Search', $search);
    }
}

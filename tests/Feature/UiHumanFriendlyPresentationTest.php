<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiHumanFriendlyPresentationTest extends TestCase
{
    public function test_business_mix_sits_below_the_primary_navigation(): void
    {
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));

        $this->assertIsString($layout);
        $navigationEnd = strpos($layout, '</nav>');
        $businessMix = strpos($layout, 'data-zazu-workspace-profile');
        $sidebarFooter = strpos($layout, 'zazu-sidebar-footer');

        $this->assertNotFalse($navigationEnd);
        $this->assertNotFalse($businessMix);
        $this->assertNotFalse($sidebarFooter);
        $this->assertLessThan($businessMix, $navigationEnd);
        $this->assertLessThan($sidebarFooter, $businessMix);
    }

    public function test_dashboard_uses_a_visible_image_layer_with_a_readable_overlay(): void
    {
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-product-authority.css'));
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));

        $this->assertIsString($layout);
        $this->assertIsString($css);
        $this->assertIsString($dashboard);
        $this->assertStringContainsString("asset('images/landing/stock/hero.jpg')", $layout);
        $this->assertStringContainsString('var(--zazu-dashboard-image) !important;', $css);
        $this->assertStringContainsString('76%, transparent)', $css);
        $this->assertStringContainsString('54%, transparent)', $css);
        $this->assertStringNotContainsString('class="zazu-dash-hero-image"', $dashboard);
    }

    public function test_backup_times_are_presented_as_local_human_readable_dates(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $sentinel = file_get_contents(resource_path('views/admin/sentinel.blade.php'));

        $this->assertIsString($dashboard);
        $this->assertIsString($sentinel);
        $this->assertStringContainsString("->timezone(config('app.timezone'))->format('j M Y, H:i')", $dashboard);
        $this->assertStringContainsString("->timezone(config('app.timezone'))->format('j M Y, H:i')", $sentinel);
    }

    public function test_primary_navigation_and_dashboard_use_the_local_tabler_icon_component(): void
    {
        $layout = file_get_contents(resource_path('views/components/app-layout.blade.php'));
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $component = file_get_contents(resource_path('views/components/icon.blade.php'));
        $notices = file_get_contents(base_path('THIRD_PARTY_NOTICES.md'));

        $this->assertIsString($layout);
        $this->assertIsString($dashboard);
        $this->assertIsString($component);
        $this->assertIsString($notices);
        $this->assertStringContainsString('<x-icon name="briefcase"', $layout);
        $this->assertStringContainsString('<x-icon name="wallet"', $layout);
        $this->assertStringContainsString('<x-icon name="calendar-event"', $dashboard);
        $this->assertStringContainsString('<x-icon name="file-invoice"', $dashboard);
        $this->assertStringContainsString('stroke="currentColor"', $component);
        $this->assertStringContainsString('Tabler Icons', $notices);
        $this->assertStringContainsString('MIT License', $notices);
        $this->assertStringNotContainsString('https://cdn.', $component);
    }
}

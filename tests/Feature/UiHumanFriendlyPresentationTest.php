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
        $this->assertStringContainsString("->timezone(config('app.display_timezone', config('app.timezone')))->format('j M Y, H:i')", $dashboard);
        $this->assertStringContainsString("->timezone(config('app.display_timezone', config('app.timezone')))->format('j M Y, H:i')", $sentinel);
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

    public function test_export_and_recovery_actions_use_local_semantic_tabler_icons(): void
    {
        $exports = file_get_contents(resource_path('views/exports/index.blade.php'));
        $component = file_get_contents(resource_path('views/components/icon.blade.php'));

        $this->assertIsString($exports);
        $this->assertIsString($component);
        $this->assertStringContainsString('<x-icon name="download"', $exports);
        $this->assertStringContainsString('<x-icon name="upload"', $exports);
        $this->assertStringContainsString('<x-icon name="lock"', $exports);
        $this->assertStringContainsString('<x-icon name="shield-check"', $exports);
        $this->assertStringContainsString("'download' =>", $component);
        $this->assertStringContainsString("'upload' =>", $component);
        $this->assertStringContainsString("'shield-check' =>", $component);
        $this->assertStringNotContainsString('>↓</div>', $exports);
        $this->assertStringNotContainsString('>↥</div>', $exports);
    }

    public function test_business_wallpaper_is_visible_on_dashboard_pages(): void
    {
        $css = file_get_contents(resource_path('css/zazu-product-authority.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('body.zazu-dashboard-page.zazu-has-wallpaper {', $css);
        $this->assertStringContainsString('var(--zazu-wallpaper) !important;', $css);
        $this->assertStringContainsString('body.zazu-dashboard-page.zazu-has-wallpaper .zazu-content', $css);
        $this->assertStringContainsString('background-attachment: scroll, scroll !important;', $css);
        $this->assertStringContainsString('html[data-theme="dark"] body.zazu-dashboard-page.zazu-has-wallpaper', $css);
    }

    public function test_all_user_facing_health_timestamps_use_local_human_dates(): void
    {
        $owner = file_get_contents(resource_path('views/owner/index.blade.php'));
        $admin = file_get_contents(resource_path('views/admin/sentinel.blade.php'));
        $recovery = file_get_contents(resource_path('views/sentinel/local-recovery.blade.php'));
        $config = file_get_contents(base_path('config/app.php'));
        $environment = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString("->timezone(config('app.display_timezone', config('app.timezone')))->format('j M Y, H:i')", $owner);
        $this->assertStringContainsString("->timezone(config('app.display_timezone', config('app.timezone')))->format('j M Y, H:i')", $admin);
        $this->assertStringContainsString("->timezone(config('app.display_timezone', config('app.timezone')))->format('j M Y, H:i')", $recovery);
        $this->assertStringContainsString("'display_timezone' => env('ZAZU_DISPLAY_TIMEZONE', 'Africa/Johannesburg')", $config);
        $this->assertStringContainsString('ZAZU_DISPLAY_TIMEZONE=Africa/Johannesburg', $environment);
        $this->assertStringContainsString("'timezone' => 'UTC'", $config);
    }

    public function test_form_controls_use_readable_type_and_bounded_wide_fields(): void
    {
        $css = file_get_contents(resource_path('css/zazu-product-authority.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('max-width: 760px;', $css);
        $this->assertStringContainsString('font-size: 12px;', $css);
        $this->assertStringContainsString('font-size: 14px;', $css);
        $this->assertStringContainsString('font-size: 16px;', $css);
        $this->assertStringContainsString('@media (max-width: 760px)', $css);
    }

    public function test_dashboard_metric_colours_are_semantic_not_position_based(): void
    {
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $css = file_get_contents(resource_path('css/zazu-product-authority.css'));

        $this->assertStringContainsString('zazu-dash-metric--work', $dashboard);
        $this->assertStringContainsString('zazu-dash-metric--quotes', $dashboard);
        $this->assertStringContainsString('zazu-dash-metric--customers', $dashboard);
        $this->assertStringContainsString('zazu-dash-metric--revenue', $dashboard);
        $this->assertStringContainsString('zazu-dash-overview .zazu-dash-metric:nth-child(n)', $css);
        $this->assertStringContainsString('var(--zazu-warning-soft)', $css);
        $this->assertStringContainsString('var(--zazu-success-soft)', $css);
    }

    public function test_work_quotes_and_customers_use_local_semantic_status_and_action_icons(): void
    {
        $work = file_get_contents(resource_path('views/work/index.blade.php'));
        $quotes = file_get_contents(resource_path('views/quotes/index.blade.php'));
        $customers = file_get_contents(resource_path('views/customers/index.blade.php'));
        $component = file_get_contents(resource_path('views/components/icon.blade.php'));

        $this->assertIsString($work);
        $this->assertIsString($quotes);
        $this->assertIsString($customers);
        $this->assertIsString($component);

        $this->assertStringContainsString('$statusIcon', $work);
        $this->assertStringContainsString('<x-icon name="{{ $statusIcon }}"', $work);
        $this->assertStringContainsString("'circle-check'", $work);
        $this->assertStringContainsString("'clock'", $work);
        $this->assertStringContainsString("'circle-x'", $work);
        $this->assertStringContainsString('<x-icon name="{{ $quoteStatusIcon }}"', $quotes);
        $this->assertStringContainsString('<x-icon name="upload"', $customers);
        $this->assertStringContainsString('<x-icon name="briefcase"', $customers);
        $this->assertStringContainsString('<x-icon name="eye"', $customers);
        $this->assertStringContainsString('<x-icon name="trash"', $work);
        $this->assertStringContainsString("'circle-check' =>", $component);
        $this->assertStringContainsString("'circle-x' =>", $component);
        $this->assertStringContainsString("'eye' =>", $component);
        $this->assertStringContainsString("'trash' =>", $component);

        $finance = file_get_contents(resource_path('views/finance/index.blade.php'));
        $purchasing = file_get_contents(resource_path('views/purchasing/index.blade.php'));
        $inventory = file_get_contents(resource_path('views/inventory/create.blade.php'));
        $expense = file_get_contents(resource_path('views/finance/expense-create.blade.php'));
        $payment = file_get_contents(resource_path('views/finance/payment-create.blade.php'));

        $this->assertStringContainsString('$invoiceStatusIcon', $finance);
        $this->assertStringContainsString('$expenseStatusIcon', $finance);
        $this->assertStringContainsString('<x-icon name="{{ $invoiceStatusIcon }}"', $finance);
        $this->assertStringContainsString('<x-icon name="{{ $expenseStatusIcon }}"', $finance);
        $this->assertStringContainsString('$orderStatusIcon', $purchasing);
        $this->assertStringContainsString('<x-icon name="{{ $orderStatusIcon }}"', $purchasing);
        $this->assertStringContainsString("'received' => 'circle-check'", $purchasing);
        $this->assertStringContainsString("'cancelled' => 'circle-x'", $purchasing);

        foreach ([$inventory, $expense, $payment] as $view) {
            $this->assertIsString($view);
            $this->assertStringContainsString('class="zazu-label"', $view);
            $this->assertStringContainsString('class="zazu-input"', $view);
            $this->assertStringContainsString('class="zazu-select"', $view);
            $this->assertStringContainsString('class="zazu-actionbar zazu-actionbar-sticky"', $view);
        }

        // Inventory item definitions have no notes column; operational notes belong to stock movements.
        foreach ([$expense, $payment] as $view) {
            $this->assertStringContainsString('class="zazu-textarea"', $view);
        }

        $this->assertStringContainsString('data-zazu-offline-entity="inventory_item"', $inventory);
        $this->assertStringContainsString('data-zazu-offline-entity="expense"', $expense);
        $this->assertStringContainsString('data-zazu-offline-entity="payment"', $payment);

        $purchaseCreate = file_get_contents(resource_path('views/purchasing/create.blade.php'));
        $this->assertIsString($purchaseCreate);
        $this->assertGreaterThanOrEqual(15, substr_count($purchaseCreate, 'class="zazu-label"'));
        $this->assertStringContainsString('class="zazu-form p-5"', $purchaseCreate);
        $this->assertStringContainsString('data-add-purchase-line', $purchaseCreate);
        $this->assertStringContainsString('id="purchase-line-template"', $purchaseCreate);
        $this->assertStringContainsString('data-zazu-offline-entity="purchase_order"', $purchaseCreate);
        $this->assertStringContainsString('class="zazu-actionbar zazu-actionbar-sticky"', $purchaseCreate);
        $this->assertStringNotContainsString('<span>Job (optional)</span>', $purchaseCreate);
        $this->assertStringNotContainsString('<span>Supplier ', $purchaseCreate);
    }}

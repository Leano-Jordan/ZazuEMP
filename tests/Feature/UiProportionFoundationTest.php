<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiProportionFoundationTest extends TestCase
{
    public function test_canonical_visual_tokens_use_the_blue_reference_family_and_disciplined_proportions(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--zazu-primary: #1B67C9;', $css);
        $this->assertStringContainsString('--zazu-primary-soft: #C9DFF3;', $css);
        $this->assertStringContainsString('--zazu-primary-deep: #1556A9;', $css);
        $this->assertStringContainsString('--zazu-page: #D9E8F5;', $css);
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

}

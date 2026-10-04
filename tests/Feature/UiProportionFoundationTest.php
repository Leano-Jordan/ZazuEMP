<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiProportionFoundationTest extends TestCase
{
    public function test_final_visual_layer_uses_the_canonical_brand_family_and_disciplined_proportions(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--zazu-blue-brand: #3949E8;', $css);
        $this->assertStringContainsString('--zazu-blue-vivid: #5B5CFF;', $css);
        $this->assertStringContainsString('--zazu-accent: #008E88;', $css);
        $this->assertStringContainsString('--zazu-content-max: 1320px;', $css);
        $this->assertStringContainsString('--zazu-ui-radius: 10px;', $css);
        $this->assertStringContainsString('max-width: 500px;', $css);
        $this->assertStringContainsString('max-width: 320px;', $css);
        $this->assertStringContainsString('max-width: 760px;', $css);
    }

    public function test_authentication_submit_remains_intentionally_full_width(): void
    {
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.zazu-auth-submit {', $css);
        $this->assertStringContainsString('    width: 100%;', $css);
        $this->assertStringContainsString('    max-width: none;', $css);
    }
}

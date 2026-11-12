<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiButtonFocusRingTest extends TestCase
{
    public function test_shared_buttons_only_receive_the_focus_ring_when_keyboard_focused(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertDoesNotMatchRegularExpression(
            '/\\.zazu-btn\\s*,\\s*\\.zazu-btn:focus-visible/s',
            $css
        );
        $this->assertStringContainsString('.zazu-btn:focus-visible,', $css);
        $this->assertStringContainsString('    outline: 3px solid var(--zazu-focus);', $css);
        $this->assertStringContainsString('    outline-offset: 3px;', $css);
    }
}

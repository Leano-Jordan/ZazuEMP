<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustedProxySchemeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/__director/proxy-scheme', function (Request $request) {
            return response()->json(['secure' => $request->isSecure()]);
        });
    }

    public function test_local_reverse_proxy_can_signal_https(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('/__director/proxy-scheme')
            ->assertOk()
            ->assertJson(['secure' => true]);
    }

    public function test_untrusted_lan_client_cannot_spoof_https_with_forwarded_headers(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '192.0.2.25',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('/__director/proxy-scheme')
            ->assertOk()
            ->assertJson(['secure' => false]);
    }
}

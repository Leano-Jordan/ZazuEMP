<?php

namespace Tests\Feature;

use App\Support\ZazuLocalSentinelRecovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SentinelLocalRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_recovery_screen_is_available_without_authentication(): void
    {
        $this->get('/api/sentinel/recovery')
            ->assertOk()
            ->assertSee('Sentinel Local Recovery')
            ->assertSee('zazu:sentinel-recovery-code')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_local_recovery_is_not_available_over_the_lan(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.25'])
            ->get('/api/sentinel/recovery')
            ->assertNotFound();
    }

    public function test_local_recovery_routes_do_not_use_the_api_throttle_cache(): void
    {
        $route = app('router')->getRoutes()->getByName('sentinel.local-recovery.verify');

        $this->assertNotNull($route);
        $this->assertNotContains(
            \\Illuminate\\Routing\\Middleware\\ThrottleRequests::class,
            app('router')->gatherRouteMiddleware($route)
        );
    }

    public function test_local_recovery_rejects_cross_origin_code_submission(): void
    {
        $this->withHeaders([
            'Origin' => 'http://attacker.example',
            'Sec-Fetch-Site' => 'cross-site',
        ])->post('/api/sentinel/recovery', ['code' => str_repeat('a', 64)])
            ->assertForbidden();
    }

    public function test_local_recovery_rejects_same_host_on_a_different_port(): void
    {
        $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Sec-Fetch-Site' => 'same-site',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post('/api/sentinel/recovery', ['code' => str_repeat('a', 64)])
            ->assertForbidden();
    }

    public function test_local_recovery_code_runs_health_check_once_without_login(): void
    {
        $recovery = app(ZazuLocalSentinelRecovery::class);
        $code = $recovery->issue();

        $this->withHeaders([
            'Origin' => 'http://localhost',
            'Sec-Fetch-Site' => 'same-origin',
        ])->post('/api/sentinel/recovery', ['code' => $code])
            ->assertOk()
            ->assertSee('Installation health')
            ->assertSee('Overall status:');

        $this->withHeaders([
            'Origin' => 'http://localhost',
            'Sec-Fetch-Site' => 'same-origin',
        ])->post('/api/sentinel/recovery', ['code' => $code])
            ->assertForbidden()
            ->assertSee('invalid, expired or already used');
    }
}

<?php

namespace Tests\Feature;

use App\Support\ZazuLocalSentinelRecovery;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
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
            ->assertDontSee('Database connection')
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
        $middleware = app('router')->gatherRouteMiddleware($route);

        foreach ([Authenticate::class, StartSession::class, ThrottleRequests::class] as $blockedMiddleware) {
            $this->assertNotContains($blockedMiddleware, $middleware);
        }
    }

    public function test_local_recovery_rejects_cross_origin_code_submission(): void
    {
        $this->withHeaders([
            'Origin' => 'http://attacker.example',
            'Sec-Fetch-Site' => 'cross-site',
        ])->post('/api/sentinel/recovery', ['code' => str_repeat('a', 64)])
            ->assertForbidden();
    }

    public function test_local_recovery_rejects_content_types_that_only_start_with_form_media_type(): void
    {
        $this->withHeaders([
            'Origin' => 'http://localhost',
            'Sec-Fetch-Site' => 'same-origin',
            'Content-Type' => 'application/x-www-form-urlencoded-evil',
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

    public function test_local_recovery_rejects_expired_and_malformed_challenges(): void
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'zazu-recovery-test-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700, true);
        $path = $directory.DIRECTORY_SEPARATOR.'challenge.json';
        $recovery = new ZazuLocalSentinelRecovery($path);
        $code = str_repeat('a', 64);

        try {
            file_put_contents($path, json_encode([
                'token_hash' => hash('sha256', $code),
                'expires_at' => time() - 1,
            ], JSON_THROW_ON_ERROR));

            $this->assertFalse($recovery->consume($code));

            file_put_contents($path, '{malformed');

            $this->assertFalse($recovery->consume($code));
            $this->assertFalse($recovery->consume('not-a-recovery-code'));
        } finally {
            @unlink($path);
            @unlink($path.'.lock');
            @rmdir($directory);
        }
    }

    public function test_local_recovery_code_runs_health_check_once_without_login(): void
    {
        $recovery = app(ZazuLocalSentinelRecovery::class);
        $code = $recovery->issue();

        $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '8000',
            'HTTP_HOST' => 'localhost:8000',
            'HTTPS' => 'off',
        ])->withHeaders([
            'Origin' => 'http://localhost:8000',
            'Sec-Fetch-Site' => 'same-origin',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post('/api/sentinel/recovery', ['code' => $code])
            ->assertOk()
            ->assertSee('Installation health')
            ->assertSee('Overall status:');

        $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '8000',
            'HTTP_HOST' => 'localhost:8000',
            'HTTPS' => 'off',
        ])->withHeaders([
            'Origin' => 'http://localhost:8000',
            'Sec-Fetch-Site' => 'same-origin',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post('/api/sentinel/recovery', ['code' => $code])
            ->assertForbidden()
            ->assertSee('invalid, expired or already used');
    }
}

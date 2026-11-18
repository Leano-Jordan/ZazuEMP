<?php

namespace Tests\Feature;

use App\Support\ZazuSentinelHealth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SentinelRescueTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'zazu-sentinel-rescue-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($this->directory);
        app()->instance(ZazuSentinelHealth::class, new ZazuSentinelHealth($this->directory));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_signed_rescue_page_loads_without_authentication_or_database_sessions(): void
    {
        config([
            'database.default' => 'sentinel_database_unavailable',
            'session.driver' => 'database',
        ]);

        $url = URL::temporarySignedRoute('sentinel.rescue', now()->addDays(365));

        $this->get($url)
            ->assertOk()
            ->assertSee('Sentinel Rescue')
            ->assertSee('does not require your normal Zazu sign-in')
            ->assertSee('Diagnostic write/read test')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }

    public function test_rescue_route_rejects_a_missing_signature(): void
    {
        $this->get('/api/sentinel/rescue')->assertForbidden();
    }
}

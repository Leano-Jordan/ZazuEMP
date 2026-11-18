<?php

namespace Tests\Feature;

use App\Support\ZazuSentinelHealth;
use Illuminate\Support\Facades\DB;
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
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->directory,
            'session.driver' => 'database',
        ]);
        DB::purge('sqlite');

        $incidentDirectory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($incidentDirectory);

        try {
            $url = URL::temporarySignedRoute('sentinel.rescue', now()->addDays(365));

            $this->get($url)
                ->assertOk()
                ->assertSee('Sentinel Rescue')
                ->assertSee('does not require your normal Zazu sign-in')
                ->assertSee('Database connection')
                ->assertSee('DB-001')
                ->assertSee('sqlite_open_failed')
                ->assertSee('Download safe DB report')
                ->assertSee('Diagnostic write/read test')
                ->assertHeader('Cache-Control', 'no-store, private')
                ->assertHeader('Referrer-Policy', 'no-referrer');
        } finally {
            File::deleteDirectory($incidentDirectory);
        }
    }

    public function test_rescue_route_rejects_a_missing_signature(): void
    {
        $this->get('/api/sentinel/rescue')->assertForbidden();
    }
}

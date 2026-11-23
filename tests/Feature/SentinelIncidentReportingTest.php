<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class SentinelIncidentReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sentinel_overview_counts_all_retained_fingerprints_not_only_the_top_hundred(): void
    {
        $admin = \App\Models\User::factory()->create(['email' => 'admin@zazu.test']);
        Config::set('zazu.platform_admin_email', 'admin@zazu.test');

        $directory = storage_path('app/private/zazu-diagnostics');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);

        $records = [];
        for ($index = 1; $index <= 100; $index++) {
            $fingerprint = sprintf('ZAZU-%016X', $index);
            foreach (['10:00:00', '10:01:00'] as $time) {
                $records[] = [
                    'request_id' => (string) Str::uuid(),
                    'failure_fingerprint' => $fingerprint,
                    'error_code' => 'APP-001',
                    'timestamp' => '2026-11-18T'.$time.'+02:00',
                ];
            }
        }

        // This low-frequency fingerprint is outside the 100-row display list,
        // but must still contribute to totals and the true latest-incident time.
        $records[] = [
            'request_id' => (string) Str::uuid(),
            'failure_fingerprint' => 'ZAZU-FFFFFFFFFFFFFFFF',
            'error_code' => 'APP-002',
            'timestamp' => '2026-11-18T23:00:00+02:00',
        ];

        File::put(
            $directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            implode(PHP_EOL, array_map(
                static fn (array $record): string => json_encode($record, JSON_THROW_ON_ERROR),
                $records,
            )).PHP_EOL,
        );

        try {
            $this->actingAs($admin)
                ->get(route('admin.sentinel'))
                ->assertOk()
                ->assertSee('101</strong>', false)
                ->assertSee('201</strong>', false)
                ->assertSee('2026-11-18T23:00:00+02:00');

            $this->actingAs($admin)
                ->get(route('admin.sentinel', ['q' => 'APP-002']))
                ->assertOk()
                ->assertSee('ZAZU-FFFFFFFFFFFFFFFF')
                ->assertSee('APP-002')
                ->assertDontSee('ZAZU-0000000000000001');
        } finally {
            File::deleteDirectory($directory);
        }
    }
}

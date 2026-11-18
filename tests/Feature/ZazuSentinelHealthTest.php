<?php

namespace Tests\Feature;

use App\Support\ZazuSentinelHealth;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ZazuSentinelHealthTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'zazu-sentinel-test-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($this->directory);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    public function test_sentinel_checks_its_private_ledger_and_cleans_its_write_probe(): void
    {
        File::put(
            $this->directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            json_encode([
                'request_id' => 'b17e9a51-891c-48b6-9d71-1f24e6ab4c82',
                'error_code' => 'DB-001',
            ], JSON_THROW_ON_ERROR).PHP_EOL,
        );

        $report = (new ZazuSentinelHealth($this->directory))->inspect();

        $this->assertSame('healthy', $report['status']);
        $this->assertSame('healthy', $this->check($report, 'diagnostic_directory')['status']);
        $this->assertSame('healthy', $this->check($report, 'current_ledger')['status']);
        $this->assertSame(1, $this->check($report, 'current_ledger')['records']);
        $this->assertSame('healthy', $this->check($report, 'write_probe')['status']);
        $this->assertSame([], glob($this->directory.DIRECTORY_SEPARATOR.'sentinel-health-*.tmp') ?: []);
    }

    public function test_sentinel_flags_malformed_ledger_lines_without_exposing_the_line_contents(): void
    {
        File::put(
            $this->directory.DIRECTORY_SEPARATOR.'incidents.jsonl',
            "{malformed private payload customer-email@example.test".PHP_EOL,
        );

        $report = (new ZazuSentinelHealth($this->directory))->inspect();

        $this->assertSame('attention', $report['status']);
        $this->assertSame('attention', $this->check($report, 'current_ledger')['status']);
        $this->assertSame(1, $this->check($report, 'current_ledger')['malformed_lines']);
        $this->assertStringNotContainsString(
            'customer-email@example.test',
            json_encode($report, JSON_THROW_ON_ERROR),
        );
        $this->assertSame([], glob($this->directory.DIRECTORY_SEPARATOR.'sentinel-health-*.tmp') ?: []);
    }

    public function test_sentinel_reports_a_missing_diagnostics_directory_without_creating_it(): void
    {
        File::deleteDirectory($this->directory);

        $report = (new ZazuSentinelHealth($this->directory))->inspect();

        $this->assertSame('failed', $report['status']);
        $this->assertSame('failed', $this->check($report, 'diagnostic_directory')['status']);
        $this->assertSame('not_checked', $this->check($report, 'write_probe')['status']);
        $this->assertDirectoryDoesNotExist($this->directory);
    }

    private function check(array $report, string $key): array
    {
        foreach ($report['checks'] as $check) {
            if ($check['key'] === $key) {
                return $check;
            }
        }

        $this->fail('Sentinel health check not found: '.$key);
    }
}

<?php

namespace Tests\Unit;

use App\Support\ProcessFileStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ProcessFileStreamTest extends TestCase
{
    public function test_process_output_is_streamed_to_disk_without_truncation(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-stream-');
        $this->assertNotFalse($path);

        try {
            $process = new Process([
                PHP_BINARY,
                '-r',
                'fwrite(STDOUT, str_repeat("z", 4194304));',
            ]);
            $process->setTimeout(30);

            ProcessFileStream::runToFile($process, $path, 'Test dump failed:');

            $this->assertSame(4194304, filesize($path));
            $this->assertSame(hash('sha256', str_repeat('z', 4194304)), hash_file('sha256', $path));
        } finally {
            if (is_string($path) && is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_process_input_is_streamed_from_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-stream-input-');
        $payload = str_repeat('zazu-recovery-', 300000);
        $expectedHash = hash('sha256', $payload);
        file_put_contents($path, $payload);

        try {
            $script = '$hash = hash_init("sha256"); while (!feof(STDIN)) { $chunk = fread(STDIN, 8192); if ($chunk !== false && $chunk !== "") { hash_update($hash, $chunk); } } if (!hash_equals("'.$expectedHash.'", hash_final($hash))) { exit(9); }';
            $process = new Process([PHP_BINARY, '-r', $script]);
            $process->setTimeout(30);

            ProcessFileStream::runFromFile($process, $path, 'Test import failed:');

            $this->assertTrue($process->isSuccessful());
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_failed_dump_removes_partial_output_and_keeps_bounded_diagnostic(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-stream-failure-');
        $process = new Process([
            PHP_BINARY,
            '-r',
            'fwrite(STDOUT, "partial-dump"); fwrite(STDERR, str_repeat("E", 12000)); exit(7);',
        ]);
        $process->setTimeout(30);

        try {
            ProcessFileStream::runToFile($process, $path, 'Test dump failed:');
            $this->fail('A failed database process must not leave a usable dump file.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Test dump failed:', $exception->getMessage());
            $this->assertLessThanOrEqual(8192 + strlen('Test dump failed: '), strlen($exception->getMessage()));
            $this->assertFileDoesNotExist($path);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}

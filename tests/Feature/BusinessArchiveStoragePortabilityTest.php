<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Services\BusinessArchiveFileCollector;
use App\Services\BusinessArchiveZipWriter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class BusinessArchiveStoragePortabilityTest extends TestCase
{
    public function test_archive_collector_uses_storage_streams_instead_of_local_disk_paths(): void
    {
        $business = new Business;
        $business->forceFill(['id' => 42]);

        $sourcePath = 'jobs/42/attachments/stream-only.pdf';
        $source = fopen('php://temp', 'w+b');
        $this->assertIsResource($source);
        fwrite($source, 'stream-backed private attachment');
        rewind($source);

        $disk = \Mockery::mock();
        $disk->shouldReceive('exists')
            ->with($sourcePath)
            ->twice()
            ->andReturn(true);
        $disk->shouldReceive('readStream')
            ->with($sourcePath)
            ->once()
            ->andReturn($source);

        Storage::shouldReceive('disk')
            ->with('private')
            ->twice()
            ->andReturn($disk);

        $files = app(BusinessArchiveFileCollector::class)->collectFiles($business, [
            'business_capabilities' => [],
            'customers' => [],
            'compliance_documents' => [],
            'event_attachments' => [[
                'id' => 7,
                'path' => $sourcePath,
                'disk' => 'private',
            ]],
        ]);

        $stagedPath = array_values($files['sources'])[0];

        try {
            $this->assertFileExists($stagedPath);
            $this->assertSame('stream-backed private attachment', file_get_contents($stagedPath));
        } finally {
            if (is_file($stagedPath) && ! is_link($stagedPath)) {
                @unlink($stagedPath);
            }
        }
    }

    public function test_zip_writer_removes_staged_sources_after_successful_archive_creation(): void
    {
        $business = new Business;
        $business->forceFill([
            'id' => 42,
            'name' => 'Archive source',
            'currency' => 'ZAR',
        ]);

        $sourcePath = tempnam(sys_get_temp_dir(), 'zazu-export-cleanup-test-');
        $this->assertNotFalse($sourcePath);
        file_put_contents($sourcePath, 'private staged payload');

        $archivePath = null;

        try {
            $archivePath = app(BusinessArchiveZipWriter::class)->create($business, [], [
                'manifest' => [],
                'sources' => ['payload/files/source.bin' => $sourcePath],
            ]);

            $this->assertFileExists($archivePath);
            $this->assertFileDoesNotExist($sourcePath);

            $zip = new ZipArchive;
            $this->assertSame(true, $zip->open($archivePath));
            $this->assertSame('private staged payload', $zip->getFromName('payload/files/source.bin'));
            $zip->close();
        } finally {
            if (is_string($archivePath) && is_file($archivePath)) {
                @unlink($archivePath);
            }
            if (is_file($sourcePath) && ! is_link($sourcePath)) {
                @unlink($sourcePath);
            }
        }
    }

    public function test_zip_writer_removes_staged_sources_when_archive_creation_fails(): void
    {
        $business = new Business;
        $business->forceFill([
            'id' => 42,
            'name' => 'Archive source',
            'currency' => 'ZAR',
        ]);

        $sourcePath = tempnam(sys_get_temp_dir(), 'zazu-export-failure-test-');
        $this->assertNotFalse($sourcePath);
        file_put_contents($sourcePath, 'private staged payload');

        $recursiveManifest = [];
        $recursiveManifest['self'] = &$recursiveManifest;

        try {
            try {
                app(BusinessArchiveZipWriter::class)->create($business, [], [
                    'manifest' => $recursiveManifest,
                    'sources' => ['payload/files/source.bin' => $sourcePath],
                ]);
                $this->fail('Archive creation should fail when its manifest cannot be encoded.');
            } catch (\JsonException) {
                $this->assertFileDoesNotExist($sourcePath);
            }
        } finally {
            if (is_file($sourcePath) && ! is_link($sourcePath)) {
                @unlink($sourcePath);
            }
        }
    }
}

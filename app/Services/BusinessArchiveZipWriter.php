<?php

namespace App\Services;

use App\Models\Business;
use RuntimeException;
use ZipArchive;

/**
 * Creates and verifies the portable business archive container.
 */
final class BusinessArchiveZipWriter
{
    private const MAX_UNCOMPRESSED_BYTES = 262144000;

    private const MAX_ENTRIES = 5000;

    /**
     * @param  array<string,mixed>  $data
     * @param  array{manifest:array,sources:array<string,string>}  $files
     */
    public function create(Business $business, array $data, array $files): string
    {
        $archivePath = tempnam(sys_get_temp_dir(), 'zazu-business-archive-');
        if ($archivePath === false) {
            throw new RuntimeException('Unable to create a temporary business archive.');
        }

        $zip = new ZipArchive;
        if ($zip->open($archivePath, ZipArchive::OVERWRITE) !== true) {
            @unlink($archivePath);
            throw new RuntimeException('Unable to open the business archive.');
        }

        try {
            $this->addManifest($zip, $business, $data, $files['manifest']);
            $this->addDataset($zip, $data);
            $this->addFileEntries($zip, $files['sources']);
        } catch (\Throwable $exception) {
            $zip->close();
            @unlink($archivePath);

            throw $exception;
        }

        if (! $zip->close()) {
            @unlink($archivePath);
            throw new RuntimeException('The business archive could not be finalized.');
        }

        try {
            $this->assertCompletedArchive($archivePath, $files['sources']);
        } catch (\Throwable $exception) {
            @unlink($archivePath);

            throw $exception;
        }

        return $archivePath;
    }

    private function addManifest(ZipArchive $zip, Business $business, array $data, array $fileManifest): void
    {
        $manifest = [
            'format' => 'zazu-business-archive',
            'version' => 1,
            'created_at' => now()->toIso8601String(),
            'source_business' => [
                'id' => (int) $business->id,
                'name' => $business->name,
                'currency' => $business->currency,
                'email' => $business->email,
                'phone' => $business->phone,
                'address' => $business->address,
                'website' => $business->website,
                'tax_number' => $business->tax_number,
                // The file manifest references these source paths so archive
                // validation can prove each branding payload belongs to this
                // business before restoring it to the authenticated media route.
                'logo_path' => $business->logo_path,
                'dashboard_image_path' => $business->dashboard_image_path,
                'wallpaper_path' => $business->wallpaper_path,
            ],
            'tables' => array_map('count', $data),
            'files' => $fileManifest,
        ];

        if (! $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT))) {
            throw new RuntimeException('The business archive manifest could not be added to the ZIP file.');
        }
    }

    private function addDataset(ZipArchive $zip, array $data): void
    {
        if (! $zip->addFromString('data.json', json_encode($data, JSON_THROW_ON_ERROR))) {
            throw new RuntimeException('The business archive records could not be added to the ZIP file.');
        }
    }

    /**
     * @param  array<string,string>  $sources
     */
    private function addFileEntries(ZipArchive $zip, array $sources): void
    {
        foreach ($sources as $entry => $sourcePath) {
            if (! $zip->addFile($sourcePath, $entry)) {
                throw new RuntimeException('A business file could not be added to the archive.');
            }
        }
    }

    /**
     * @param  array<string,string>  $sources
     */
    private function assertCompletedArchive(string $archivePath, array $sources): void
    {
        $zip = new ZipArchive;
        if ($zip->open($archivePath, ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('The completed business archive failed ZIP integrity validation.');
        }

        try {
            $this->assertArchiveEntryCount($zip, $sources);
            $this->assertArchiveEntries($zip, $sources);
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array<string,string>  $sources
     */
    private function assertArchiveEntryCount(ZipArchive $zip, array $sources): void
    {
        $expectedCount = count($sources) + 2;
        if ($zip->numFiles !== $expectedCount || $zip->numFiles > self::MAX_ENTRIES) {
            throw new RuntimeException('The completed business archive has an invalid entry count.');
        }
    }

    /**
     * @param  array<string,string>  $sources
     */
    private function assertArchiveEntries(ZipArchive $zip, array $sources): void
    {
        $expectedEntries = array_fill_keys(['manifest.json', 'data.json'], true);
        foreach ($sources as $entry => $_sourcePath) {
            $expectedEntries[$entry] = true;
        }

        $totalBytes = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            $entry = $stat['name'] ?? null;
            if (! is_string($entry) || ! isset($expectedEntries[$entry]) || ! isset($stat['size'])) {
                throw new RuntimeException('The completed business archive has an unexpected or invalid entry.');
            }

            unset($expectedEntries[$entry]);
            $totalBytes += (int) $stat['size'];
            if ($totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                throw new RuntimeException('The business archive exceeds the 250 MB restore limit.');
            }

            $this->assertSourceFileSize($entry, (int) $stat['size'], $sources);
        }

        if ($expectedEntries !== []) {
            throw new RuntimeException('The completed business archive is missing a required entry.');
        }
    }

    /**
     * @param  array<string,string>  $sources
     */
    private function assertSourceFileSize(string $entry, int $archiveBytes, array $sources): void
    {
        if (! isset($sources[$entry])) {
            return;
        }

        $sourceBytes = filesize($sources[$entry]);
        if ($sourceBytes === false || $archiveBytes !== $sourceBytes) {
            throw new RuntimeException('A business file was not completely added to the archive.');
        }
    }
}

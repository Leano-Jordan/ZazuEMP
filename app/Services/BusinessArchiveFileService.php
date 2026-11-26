<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Handles file payload validation and collection for business archives.
 */
final class BusinessArchiveFileService
{
    private const MAX_STAGED_BYTES = 262144000;

    public function __construct(private readonly BusinessArchivePayloadStager $stager) {}

    public function readArchiveFilePayloads(ZipArchive $zip, array $manifest, array $data): array
    {
        $allowedFileColumns = $this->allowedArchiveFileColumns();
        $references = $this->validateArchiveFileManifest($manifest);
        $this->assertArchivePayloadEntriesAreDeclared($zip, $manifest);
        $this->assertArchivePathsHavePayloads($manifest, $data, $allowedFileColumns, $references);

        $filePayloads = [];
        $totalStagedBytes = 0;

        try {
            foreach ($manifest['files'] as $file) {
                $payload = $this->readArchiveFilePayload(
                    $zip,
                    $file,
                    $manifest,
                    $data,
                    $allowedFileColumns,
                    self::MAX_STAGED_BYTES - $totalStagedBytes
                );
                $filePayloads[] = $payload;
                $totalStagedBytes += $payload['payload_bytes'];
            }

            return $filePayloads;
        } catch (\Throwable $exception) {
            foreach ($filePayloads as $payload) {
                @unlink($payload['staged_path']);
            }

            throw $exception;
        }
    }

    private function assertArchivePayloadEntriesAreDeclared(ZipArchive $zip, array $manifest): void
    {
        // Business archives are data containers, not general-purpose ZIP files.
        // Accept only the two required metadata entries and payloads explicitly
        // referenced by the validated manifest; ignore nothing silently.
        $declaredEntries = array_fill_keys(['manifest.json', 'data.json'], true);
        foreach ($manifest['files'] as $file) {
            $declaredEntries[(string) $file['entry']] = true;
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            $entry = $stat['name'] ?? null;
            if (! is_string($entry) || ! isset($declaredEntries[$entry])) {
                if (is_string($entry) && Str::startsWith($entry, 'payload/files/')) {
                    throw new RuntimeException('The archive contains an undeclared file payload.');
                }

                throw new RuntimeException('The business archive contains an unexpected ZIP entry.');
            }
        }
    }

    private function allowedArchiveFileColumns(): array
    {
        return [
            'businesses' => ['logo_path', 'dashboard_image_path', 'wallpaper_path'],
            'business_capabilities' => ['image_path'],
            'customers' => ['profile_photo_path'],
            'compliance_documents' => ['storage_path'],
            'event_attachments' => ['path'],
        ];
    }

    private function validateArchiveFileManifest(array $manifest): array
    {
        if (! is_array($manifest['files'] ?? null)) {
            throw new RuntimeException('The business archive file manifest is invalid.');
        }

        if (! array_is_list($manifest['files'])) {
            throw new RuntimeException('The business archive file manifest must be a list.');
        }

        $references = [];
        $entries = [];
        $allowedKeys = ['table', 'id', 'column', 'disk_column', 'source_disk', 'entry', 'original_name'];
        foreach ($manifest['files'] as $file) {
            if (! is_array($file) || array_diff(array_keys($file), $allowedKeys) !== []) {
                throw new RuntimeException('The archive contains an invalid business file reference.');
            }
            $id = $file['id'] ?? null;
            if (! is_string($file['table'] ?? null)
                || ! is_string($file['column'] ?? null)
                || ! is_string($file['entry'] ?? null)
                || ! is_string($file['source_disk'] ?? null)
                || (! is_int($id) && ! (is_string($id) && ctype_digit($id)))
                || (int) $id < 1
                || (array_key_exists('original_name', $file) && ! is_string($file['original_name']))) {
                throw new RuntimeException('The archive contains an invalid business file reference.');
            }
            $key = implode(':', [$file['table'], (string) (int) $id, $file['column']]);
            $entry = $file['entry'];
            if (isset($references[$key])) {
                throw new RuntimeException('The archive contains a duplicate business file reference.');
            }
            if ($entry === '' || isset($entries[$entry])) {
                throw new RuntimeException('The archive contains a duplicate or invalid file payload entry.');
            }
            $references[$key] = true;
            $entries[$entry] = true;
        }

        return $references;
    }

    private function assertArchivePathsHavePayloads(array $manifest, array $data, array $allowedColumns, array $references): void
    {
        foreach ($allowedColumns as $table => $columns) {
            $rows = $this->archiveFileRows($table, $manifest, $data, $columns);
            foreach ($rows as $row) {
                foreach ($columns as $column) {
                    if (! empty($row[$column])) {
                        $key = $table.':'.(string) ($row['id'] ?? '').':'.$column;
                        if (! isset($references[$key])) {
                            throw new RuntimeException('The archive contains a file path without its matching file payload.');
                        }
                    }
                }
            }
        }
    }

    private function archiveFileRows(string $table, array $manifest, array $data, array $columns): array
    {
        if ($table !== 'businesses') {
            return $data[$table] ?? [];
        }

        return [[
            'id' => (int) $manifest['source_business']['id'],
            ...array_intersect_key($manifest['source_business'], array_flip($columns)),
        ]];
    }

    private function readArchiveFilePayload(
        ZipArchive $zip,
        array $file,
        array $manifest,
        array $data,
        array $allowedColumns,
        int $remainingBytes
    ): array {
        $table = (string) ($file['table'] ?? '');
        $column = (string) ($file['column'] ?? '');
        $entry = (string) ($file['entry'] ?? '');
        $this->assertSafeArchiveFileReference($file, $table, $column, $entry, $allowedColumns);
        $this->assertArchiveFileRecordExists($file, $table, $manifest, $data);

        $sourcePath = $this->archiveFileSourcePath($file, $table, $column, $manifest, $data);
        if (! is_string($sourcePath) || $sourcePath === '') {
            throw new RuntimeException('The archive contains a file payload without a matching record path.');
        }
        if (! in_array(($file['source_disk'] ?? null), ['private', 'local', 'public'], true)) {
            throw new RuntimeException('The archive contains an unsupported source storage disk.');
        }

        return $this->stager->stage($zip, $entry, $remainingBytes) + $file;
    }

    private function assertSafeArchiveFileReference(array $file, string $table, string $column, string $entry, array $allowedColumns): void
    {
        if (! Str::startsWith($entry, 'payload/files/')
            || str_contains($entry, '..')
            || ! in_array($column, $allowedColumns[$table] ?? [], true)
            || ($table === 'event_attachments' && ($file['disk_column'] ?? null) !== 'disk')
            || ($table !== 'event_attachments' && ($file['disk_column'] ?? null) !== null)) {
            throw new RuntimeException('The archive contains an unsafe file reference.');
        }
    }

    private function assertArchiveFileRecordExists(array $file, string $table, array $manifest, array $data): void
    {
        $validIds = $table === 'businesses'
            ? [(int) $manifest['source_business']['id']]
            : array_map('intval', array_column($data[$table] ?? [], 'id'));
        if (! in_array((int) ($file['id'] ?? 0), $validIds, true)) {
            throw new RuntimeException('The archive contains a file reference outside its business dataset.');
        }
    }

    private function archiveFileSourcePath(array $file, string $table, string $column, array $manifest, array $data): mixed
    {
        if ($table === 'businesses') {
            return $manifest['source_business'][$column] ?? null;
        }

        return collect($data[$table] ?? [])->firstWhere('id', (int) ($file['id'] ?? 0))[$column] ?? null;
    }
}

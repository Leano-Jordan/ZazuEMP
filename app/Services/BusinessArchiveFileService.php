<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Handles file payload validation and collection for business archives.
 */
final class BusinessArchiveFileService
{
    public function readArchiveFilePayloads(ZipArchive $zip, array $manifest, array $data): array
    {
        $allowedFileColumns = $this->allowedArchiveFileColumns();
        $references = $this->validateArchiveFileManifest($manifest);
        $this->assertArchivePayloadEntriesAreDeclared($zip, $manifest);
        $this->assertArchivePathsHavePayloads($manifest, $data, $allowedFileColumns, $references);

        $filePayloads = [];
        foreach ($manifest['files'] as $file) {
            $filePayloads[] = $this->readArchiveFilePayload($zip, $file, $manifest, $data, $allowedFileColumns);
        }

        return $filePayloads;
    }

    private function assertArchivePayloadEntriesAreDeclared(ZipArchive $zip, array $manifest): void
    {
        $declaredEntries = array_fill_keys(array_column($manifest['files'], 'entry'), true);
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            $entry = $stat['name'] ?? '';
            if (is_string($entry) && Str::startsWith($entry, 'payload/files/') && ! isset($declaredEntries[$entry])) {
                throw new RuntimeException('The archive contains an undeclared file payload.');
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

        $references = [];
        $entries = [];
        foreach ($manifest['files'] as $file) {
            if (! is_array($file)) {
                throw new RuntimeException('The archive contains an invalid business file reference.');
            }
            $key = implode(':', [(string) ($file['table'] ?? ''), (string) ($file['id'] ?? ''), (string) ($file['column'] ?? '')]);
            $entry = (string) ($file['entry'] ?? '');
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

    private function readArchiveFilePayload(ZipArchive $zip, array $file, array $manifest, array $data, array $allowedColumns): array
    {
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

        $bytes = $zip->getFromName($entry);
        if ($bytes === false) {
            throw new RuntimeException('A referenced business file is missing from the archive.');
        }

        return $file + ['bytes' => $bytes];
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

    public function collectFiles(Business $business, array &$data): array
    {
        $references = [];
        $add = function (string $table, int $id, string $column, ?string $path, ?string $disk = null) use (&$references): void {
            if (! $path) {
                return;
            }
            if (Str::contains($path, ['..', '\\'])) {
                throw new RuntimeException("A referenced business file path is unsafe: {$table}#{$id}.");
            }
            $diskName = $disk ?: 'private';
            if ($disk !== null && ! in_array($diskName, ['private', 'local'], true)) {
                throw new RuntimeException("An attachment uses an unsupported storage disk: {$table}#{$id}.");
            }
            $candidates = $disk !== null ? [$diskName] : ['private', 'local', 'public'];
            $sourceDisk = null;
            foreach ($candidates as $candidate) {
                if (Storage::disk($candidate)->exists($path)) {
                    $sourceDisk = $candidate;
                    break;
                }
            }
            if ($sourceDisk === null) {
                throw new RuntimeException("A referenced business file is missing: {$table}#{$id}.");
            }
            $entry = 'payload/files/'.Str::uuid().'.bin';
            $references[] = [
                'table' => $table,
                'id' => $id,
                'column' => $column,
                'disk_column' => $disk !== null ? 'disk' : null,
                'source_disk' => $sourceDisk,
                'entry' => $entry,
                'original_name' => basename($path),
            ];
        };

        foreach (['logo_path', 'dashboard_image_path', 'wallpaper_path'] as $column) {
            $add('businesses', (int) $business->id, $column, $business->{$column});
        }
        foreach (['business_capabilities' => 'image_path', 'customers' => 'profile_photo_path', 'compliance_documents' => 'storage_path'] as $table => $column) {
            foreach ($data[$table] as $row) {
                $add($table, (int) $row['id'], $column, $row[$column] ?? null);
            }
        }
        foreach ($data['event_attachments'] as $row) {
            $add('event_attachments', (int) $row['id'], 'path', $row['path'] ?? null, $row['disk'] ?? 'private');
        }

        $contents = [];
        foreach ($references as &$reference) {
            $sourcePath = match ($reference['table']) {
                'businesses' => $business->{$reference['column']},
                default => collect($data[$reference['table']])->firstWhere('id', $reference['id'])[$reference['column']],
            };
            $contents[$reference['entry']] = Storage::disk($reference['source_disk'])->get($sourcePath);
        }
        unset($reference);

        return ['manifest' => $references, 'contents' => $contents];
    }


}

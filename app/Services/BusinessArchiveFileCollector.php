<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Collects tenant-owned files and packages their bytes for a business archive.
 */
final class BusinessArchiveFileCollector
{
    public function collectFiles(Business $business, array &$data): array
    {
        $references = [];
        $this->collectBusinessImages($business, $references);
        $this->collectDatasetFiles($data, $references);
        $this->collectAttachmentFiles($data, $references);

        return $this->readFileContents($business, $data, $references);
    }

    private function collectBusinessImages(Business $business, array &$references): void
    {
        foreach (['logo_path', 'dashboard_image_path', 'wallpaper_path'] as $column) {
            $this->appendReference($references, 'businesses', (int) $business->id, $column, $business->{$column});
        }
    }

    private function collectDatasetFiles(array $data, array &$references): void
    {
        $columns = [
            'business_capabilities' => 'image_path',
            'customers' => 'profile_photo_path',
            'compliance_documents' => 'storage_path',
        ];

        foreach ($columns as $table => $column) {
            foreach ($data[$table] as $row) {
                $this->appendReference($references, $table, (int) $row['id'], $column, $row[$column] ?? null);
            }
        }
    }

    private function collectAttachmentFiles(array $data, array &$references): void
    {
        foreach ($data['event_attachments'] as $row) {
            $this->appendReference(
                $references,
                'event_attachments',
                (int) $row['id'],
                'path',
                $row['path'] ?? null,
                $row['disk'] ?? 'private'
            );
        }
    }

    private function appendReference(
        array &$references,
        string $table,
        int $id,
        string $column,
        ?string $path,
        ?string $disk = null
    ): void {
        if (! $path) {
            return;
        }

        $this->assertSafePath($path, $table, $id);
        $sourceDisk = $this->resolveSourceDisk($path, $disk, $table, $id);

        $references[] = [
            'table' => $table,
            'id' => $id,
            'column' => $column,
            'disk_column' => $disk !== null ? 'disk' : null,
            'source_disk' => $sourceDisk,
            'entry' => 'payload/files/'.Str::uuid().'.bin',
            'original_name' => basename($path),
        ];
    }

    private function assertSafePath(string $path, string $table, int $id): void
    {
        if (Str::contains($path, ['..', '\\'])) {
            throw new RuntimeException("A referenced business file path is unsafe: {$table}#{$id}.");
        }
    }

    private function resolveSourceDisk(string $path, ?string $disk, string $table, int $id): string
    {
        $diskName = $disk ?: 'private';
        if ($disk !== null && ! in_array($diskName, ['private', 'local'], true)) {
            throw new RuntimeException("An attachment uses an unsupported storage disk: {$table}#{$id}.");
        }

        $candidates = $disk !== null ? [$diskName] : ['private', 'local', 'public'];
        foreach ($candidates as $candidate) {
            if (Storage::disk($candidate)->exists($path)) {
                return $candidate;
            }
        }

        throw new RuntimeException("A referenced business file is missing: {$table}#{$id}.");
    }

    private function readFileContents(Business $business, array $data, array $references): array
    {
        $contents = [];
        foreach ($references as $reference) {
            $sourcePath = $reference['table'] === 'businesses'
                ? $business->{$reference['column']}
                : collect($data[$reference['table']])->firstWhere('id', $reference['id'])[$reference['column']];
            $contents[$reference['entry']] = Storage::disk($reference['source_disk'])->get($sourcePath);
        }

        return ['manifest' => $references, 'contents' => $contents];
    }
}

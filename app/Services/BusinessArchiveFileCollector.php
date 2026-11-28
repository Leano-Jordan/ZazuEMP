<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Resolves tenant-owned file sources and builds business-archive file references.
 *
 * Source files are copied through the filesystem stream contract into private
 * temporary files because ZIP creation requires local file paths. This keeps
 * archive export independent of the configured storage adapter.
 */
final class BusinessArchiveFileCollector
{
    private const MAX_STAGED_BYTES = 262144000;

    private const STREAM_CHUNK_BYTES = 1048576;

    public function collectFiles(Business $business, array $data): array
    {
        $references = [];
        $this->collectBusinessImages($business, $references);
        $this->collectDatasetFiles($data, $references);
        $this->collectAttachmentFiles($data, $references);

        return $this->resolveFileSources($business, $data, $references);
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

    private function resolveFileSources(Business $business, array $data, array $references): array
    {
        $sources = [];
        $totalBytes = 0;

        try {
            foreach ($references as $reference) {
                $sourcePath = $reference['table'] === 'businesses'
                    ? $business->{$reference['column']}
                    : (collect($data[$reference['table']])->firstWhere('id', $reference['id'])[$reference['column']] ?? null);

                if (! is_string($sourcePath) || $sourcePath === '') {
                    throw new RuntimeException('A referenced business file path could not be resolved.');
                }

                $disk = Storage::disk($reference['source_disk']);
                if (! $disk->exists($sourcePath)) {
                    throw new RuntimeException('A referenced business file is missing.');
                }

                $stream = $disk->readStream($sourcePath);
                if (! is_resource($stream)) {
                    throw new RuntimeException('A referenced business file cannot be streamed into the archive.');
                }

                try {
                    [$localPath, $bytes] = $this->stageStream($stream, self::MAX_STAGED_BYTES - $totalBytes);
                } finally {
                    fclose($stream);
                }

                $sources[$reference['entry']] = $localPath;
                $totalBytes += $bytes;
            }
        } catch (Throwable $exception) {
            $this->cleanupStagedSources($sources);

            throw $exception;
        }

        return ['manifest' => $references, 'sources' => $sources];
    }

    /**
     * @param resource $source
     * @return array{0:string,1:int}
     */
    private function stageStream($source, int $remainingBytes): array
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-export-source-');
        if ($path === false) {
            throw new RuntimeException('A private business-file staging file could not be created.');
        }

        if (! @chmod($path, 0600)) {
            @unlink($path);

            throw new RuntimeException('A private business-file staging file could not be secured.');
        }

        $target = @fopen($path, 'wb');
        if ($target === false) {
            @unlink($path);

            throw new RuntimeException('A private business-file staging file could not be opened.');
        }

        $bytes = 0;

        try {
            while (! feof($source)) {
                $chunk = fread($source, self::STREAM_CHUNK_BYTES);
                if ($chunk === false) {
                    throw new RuntimeException('A business file payload could not be read from storage.');
                }

                if ($chunk === '') {
                    if (feof($source)) {
                        break;
                    }

                    throw new RuntimeException('A business file storage stream ended unexpectedly.');
                }

                $chunkBytes = strlen($chunk);
                if ($chunkBytes > $remainingBytes - $bytes) {
                    throw new RuntimeException('Business files exceed the 250 MB archive export limit.');
                }

                $this->writeAll($target, $chunk);
                $bytes += $chunkBytes;
            }

            if (! fflush($target)) {
                throw new RuntimeException('A staged business file could not be finalized.');
            }
        } catch (Throwable $exception) {
            @unlink($path);

            throw $exception;
        } finally {
            fclose($target);
        }

        return [$path, $bytes];
    }

    /** @param resource $target */
    private function writeAll($target, string $contents): void
    {
        $offset = 0;
        $length = strlen($contents);

        while ($offset < $length) {
            $written = fwrite($target, substr($contents, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('A staged business file could not be written.');
            }

            $offset += $written;
        }
    }

    private function cleanupStagedSources(array $sources): void
    {
        foreach ($sources as $path) {
            if (is_string($path) && is_file($path) && ! is_link($path)) {
                @unlink($path);
            }
        }
    }
}

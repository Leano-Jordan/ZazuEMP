<?php

namespace App\Support;

final class SecondaryBackupArchiveVerifier
{
    /**
     * @param array<string, mixed> $state
     */
    public function inspect(array $state): string
    {
        $path = $state['secondary_archive_path'] ?? null;

        if ($this->hasValidMetadata($state, $path) === false) {
            return 'invalid';
        }

        $path = (string) $path;
        clearstatcache(true, $path);

        if ($this->isRegularArchive($path) === false) {
            return 'missing';
        }

        return $this->matchesRecordedMetadata($path, $state) ? 'present' : 'changed';
    }

    /**
     * @param array<string, mixed> $state
     * @param mixed $path
     */
    private function hasValidMetadata(array $state, mixed $path): bool
    {
        return $this->isAbsolutePath($path)
            && $this->isValidSha256($state['secondary_archive_sha256'] ?? null)
            && $this->isPositiveInteger($state['secondary_archive_size_bytes'] ?? null)
            && $this->isPositiveInteger($state['secondary_archive_modified_unix'] ?? null);
    }

    private function isAbsolutePath(mixed $path): bool
    {
        return is_string($path)
            && $path !== ''
            && (
                str_starts_with($path, DIRECTORY_SEPARATOR)
                || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1
                || str_starts_with($path, '\\\\')
            );
    }

    private function isValidSha256(mixed $sha256): bool
    {
        return is_string($sha256) && preg_match('/^[a-f0-9]{64}$/i', $sha256) === 1;
    }

    private function isPositiveInteger(mixed $value): bool
    {
        return (is_int($value) || (is_string($value) && ctype_digit($value)))
            && (int) $value > 0;
    }

    private function isRegularArchive(string $path): bool
    {
        return is_file($path) && is_link($path) === false;
    }

    /**
     * @param string $path
     * @param array<string, mixed> $state
     */
    private function matchesRecordedMetadata(string $path, array $state): bool
    {
        $actualSize = @filesize($path);
        $actualModifiedUnix = @filemtime($path);

        return $actualSize !== false
            && $actualModifiedUnix !== false
            && (int) $actualSize === (int) $state['secondary_archive_size_bytes']
            && (int) $actualModifiedUnix === (int) $state['secondary_archive_modified_unix'];
    }
}

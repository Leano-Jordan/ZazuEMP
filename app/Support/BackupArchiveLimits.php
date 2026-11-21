<?php

namespace App\Support;

/**
 * Shared safety limits for installation backup and restore archives.
 *
 * The entry ceiling reserves one manifest and one database payload in
 * addition to the permitted private-storage files.
 */
final class BackupArchiveLimits
{
    public const MAX_PRIVATE_FILES = 50000;
    public const MAX_ARCHIVE_ENTRIES = self::MAX_PRIVATE_FILES + 2;
    public const MAX_UNCOMPRESSED_BYTES = 2147483648;

    public static function canIncludePrivateFiles(int $count): bool
    {
        return $count >= 0 && $count <= self::MAX_PRIVATE_FILES;
    }

    public static function canIncludeArchiveEntries(int $count): bool
    {
        return $count >= 2 && $count <= self::MAX_ARCHIVE_ENTRIES;
    }

    public static function canIncludeUncompressedBytes(int $currentBytes, int $nextFileBytes): bool
    {
        return $currentBytes >= 0
            && $nextFileBytes >= 0
            && $nextFileBytes <= self::MAX_UNCOMPRESSED_BYTES
            && $currentBytes <= self::MAX_UNCOMPRESSED_BYTES - $nextFileBytes;
    }
}

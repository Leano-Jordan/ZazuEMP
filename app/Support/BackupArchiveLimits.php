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
}

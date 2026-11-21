<?php

namespace Tests\Unit;

use App\Support\BackupArchiveLimits;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BackupArchiveLimitsTest extends TestCase
{
    #[Test]
    public function backup_entry_capacity_includes_database_and_manifest(): void
    {
        $this->assertSame(
            BackupArchiveLimits::MAX_PRIVATE_FILES + 2,
            BackupArchiveLimits::MAX_ARCHIVE_ENTRIES,
        );
        $this->assertTrue(BackupArchiveLimits::canIncludePrivateFiles(50_000));
        $this->assertFalse(BackupArchiveLimits::canIncludePrivateFiles(50_001));
        $this->assertTrue(BackupArchiveLimits::canIncludeArchiveEntries(50_002));
        $this->assertFalse(BackupArchiveLimits::canIncludeArchiveEntries(50_003));
        $this->assertFalse(BackupArchiveLimits::canIncludeArchiveEntries(1));
    }

    #[Test]
    public function backup_never_publishes_a_payload_larger_than_restore_accepts(): void
    {
        $limit = BackupArchiveLimits::MAX_UNCOMPRESSED_BYTES;

        $this->assertTrue(BackupArchiveLimits::canIncludeUncompressedBytes($limit - 1, 1));
        $this->assertTrue(BackupArchiveLimits::canIncludeUncompressedBytes(0, $limit));
        $this->assertFalse(BackupArchiveLimits::canIncludeUncompressedBytes($limit, 1));
        $this->assertFalse(BackupArchiveLimits::canIncludeUncompressedBytes(0, $limit + 1));
        $this->assertFalse(BackupArchiveLimits::canIncludeUncompressedBytes(-1, 1));
        $this->assertFalse(BackupArchiveLimits::canIncludeUncompressedBytes(1, -1));
    }
}

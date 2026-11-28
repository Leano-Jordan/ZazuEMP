<?php

namespace App\\Support;

/**
 * Prevents installation-wide recovery from silently omitting remote private files.
 *
 * The current installation archive format snapshots the local private directory.
 * Remote private disks require a provider-aware snapshot and restore implementation.
 */
final class InstallationBackupStorageGuard
{
    public function unsupportedReason(): ?string
    {
        if (config('filesystems.disks.private.driver') !== 'local') {
            return 'Installation-wide backup and restore currently support only local private storage. Remote private storage is not included; no installation data was changed.';
        }

        return null;
    }
}

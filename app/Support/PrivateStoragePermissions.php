<?php

namespace App\Support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PrivateStoragePermissions
{
    public function createDirectory(string $path): bool
    {
        if (! is_dir($path) && ! @mkdir($path, 0700, true) && ! is_dir($path)) {
            return false;
        }

        return $this->restrictDirectoryTree($path);
    }

    public function restrictDirectoryTree(string $path): bool
    {
        if (! is_dir($path) || is_link($path)) {
            return false;
        }

        // Windows access is governed by inherited NTFS ACLs, not POSIX mode bits.
        if (PHP_OS_FAMILY === 'Windows') {
            return true;
        }

        try {
            $entries = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($entries as $entry) {
                if ($entry->isLink() || (! $entry->isDir() && ! $entry->isFile())) {
                    return false;
                }

                $mode = $entry->isDir() ? 0700 : 0600;
                if (! @chmod($entry->getPathname(), $mode)) {
                    return false;
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return @chmod($path, 0700);
    }

    public function restrictFile(string $path): bool
    {
        if (! is_file($path) || is_link($path)) {
            return false;
        }

        // Windows access is governed by inherited NTFS ACLs, not POSIX mode bits.
        return PHP_OS_FAMILY === 'Windows' || @chmod($path, 0600);
    }
}

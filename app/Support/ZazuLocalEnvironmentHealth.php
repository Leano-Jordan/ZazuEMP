<?php

namespace App\Support;

final class ZazuLocalEnvironmentHealth
{
    public function inspect(): array
    {
        return [
            $this->privateStorageCheck(),
            $this->offlineAssetsCheck(),
        ];
    }

    private function privateStorageCheck(): array
    {
        $path = storage_path('app/private');
        $exists = is_dir($path);
        $readable = $exists && is_readable($path);
        $writable = $exists && is_writable($path);
        $healthy = $readable && $writable;

        return [
            'key' => 'private_storage',
            'group' => 'Storage',
            'label' => 'Private file storage',
            'status' => $healthy ? 'healthy' : 'failed',
            'message' => ! $exists
                ? 'The private file-storage folder is missing.'
                : ($healthy
                    ? 'The private file-storage folder is readable and writable.'
                    : 'The private file-storage folder is not both readable and writable. Attachments or private files may be affected.'),
        ];
    }

    private function offlineAssetsCheck(): array
    {
        $assets = [
            'service worker' => public_path('sw.js'),
            'offline client' => public_path('zazu-offline.js'),
            'web app manifest' => public_path('manifest.webmanifest'),
            'compiled asset manifest' => public_path('build/manifest.json'),
        ];
        $missing = [];

        foreach ($assets as $label => $path) {
            if (! is_file($path) || ! is_readable($path)) {
                $missing[] = $label;
            }
        }

        return [
            'key' => 'offline_assets',
            'group' => 'Offline support',
            'label' => 'Installed-app support files',
            'status' => $missing === [] ? 'healthy' : 'failed',
            'message' => $missing === []
                ? 'Core offline support files are present. This does not test browser caches, local records or phone synchronisation.'
                : 'Required installed-app files are missing or unreadable: '.implode(', ', $missing).'.',
            'missing_assets' => $missing,
        ];
    }
}

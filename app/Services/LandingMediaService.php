<?php

namespace App\Services;

use App\Models\PlatformLandingMedia;
use App\Models\PlatformLandingSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class LandingMediaService
{
    private const IMAGE_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const SLOT_DEFAULTS = [
        'hero_image' => ['column' => 'hero_image_path', 'image' => 'catering_service'],
        'operations_image' => ['column' => 'operations_image_path', 'image' => 'event_catering'],
        'resources_image' => ['column' => 'resources_image_path', 'image' => 'sound_stage'],
        'control_image' => ['column' => 'control_image_path', 'image' => 'wedding_catering'],
        'venue_image' => ['column' => 'venue_image_path', 'image' => 'venue'],
        'decor_image' => ['column' => 'decor_image_path', 'image' => 'decor'],
    ];

    public function current(): PlatformLandingSetting
    {
        return PlatformLandingSetting::current();
    }

    /**
     * Return every distinct bundled image and every safely stored admin upload.
     *
     * @return array<string, array{label: string, url: string, source: string}>
     */
    public function library(): array
    {
        return array_merge($this->bundledLibrary(), $this->uploadedLibrary());
    }

    /**
     * @return array<string, array{label: string, url: string, source: string}>
     */
    private function bundledLibrary(): array
    {
        $library = [];
        $seenPaths = [];

        foreach (config('zazu.landing_image_library', []) as $key => $entry) {
            $this->appendBundledEntry($library, $seenPaths, (string) $key, $entry);
        }

        return $library;
    }

    /**
     * @param array<string, array{label: string, url: string, source: string}> $library
     * @param array<string, bool> $seenPaths
     * @param array<string, mixed> $entry
     */
    private function appendBundledEntry(array &$library, array &$seenPaths, string $key, array $entry): void
    {
        $label = (string) ($entry['label'] ?? 'Landing image');
        $candidates = [
            ['key' => $key, 'path' => $entry['url'] ?? null, 'label' => $label],
            ['key' => $key.'_fallback', 'path' => $entry['fallback'] ?? null, 'label' => $label.' illustration'],
        ];

        foreach ($candidates as $candidate) {
            $path = (string) ($candidate['path'] ?? '');

            if (!$this->isAvailableLibraryPath($path, $seenPaths)) {
                continue;
            }

            $library[$candidate['key']] = [
                'label' => $candidate['label'],
                'url' => $path,
                'source' => 'Bundled',
            ];
            $seenPaths[$path] = true;
        }
    }

    /**
     * @param array<string, bool> $seenPaths
     */
    private function isAvailableLibraryPath(string $path, array $seenPaths): bool
    {
        return $this->isSafeLandingPath($path)
            && $this->imageFileExists($path)
            && !isset($seenPaths[$path]);
    }

    /**
     * @return array<string, array{label: string, url: string, source: string}>
     */
    private function uploadedLibrary(): array
    {
        if (!Schema::hasTable('platform_landing_media')) {
            return [];
        }

        $library = [];
        foreach (PlatformLandingMedia::query()->latest('created_at')->get() as $media) {
            $path = (string) $media->path;

            if (!$this->isSafeLandingPath($path) || !$this->imageFileExists($path)) {
                continue;
            }

            $library['uploaded_'.$media->id] = [
                'label' => $media->label,
                'url' => $path,
                'source' => 'Uploaded',
            ];
        }

        return $library;
    }

    public function images(): array
    {
        $defaults = config('zazu.landing_image_library', []);

        $images = [
            'hero' => $this->localPath($defaults['catering_service']),
            'operations' => $this->localPath($defaults['event_catering']),
            'resources' => $this->localPath($defaults['sound_stage']),
            'control' => $this->localPath($defaults['wedding_catering']),
            'venue' => $this->localPath($defaults['venue']),
            'tent' => $this->localPath($defaults['tent']),
            'decor' => $this->localPath($defaults['decor']),
        ];

        // Keep the public landing page available before the platform-media
        // migration has run during a rolling deployment.
        if (!Schema::hasTable('platform_landing_settings')) {
            return $images;
        }

        $settings = $this->current();
        foreach (self::SLOT_DEFAULTS as $slot) {
            $key = $this->imageKeyForPublicSlot($slot['image']);
            $fallback = $images[$key];

            $images[$key] = $this->usablePath($settings->{$slot['column']} ?? null, $fallback);
        }

        return $images;
    }

    private function imageKeyForPublicSlot(string $image): string
    {
        return match ($image) {
            'catering_service' => 'hero',
            'event_catering' => 'operations',
            'sound_stage' => 'resources',
            'wedding_catering' => 'control',
            default => $image,
        };
    }

    private function usablePath(?string $storedPath, string $fallback): string
    {
        return $storedPath
            && $this->isSafeLandingPath($storedPath)
            && $this->imageFileExists($storedPath)
                ? $storedPath
                : $fallback;
    }

    private function localPath(array $libraryEntry): string
    {
        $candidate = (string) ($libraryEntry['url'] ?? '');
        $fallback = (string) ($libraryEntry['fallback'] ?? '');

        if ($this->isSafeLandingPath($candidate) && $this->imageFileExists($candidate)) {
            return $candidate;
        }

        return $this->isSafeLandingPath($fallback) && $this->imageFileExists($fallback)
            ? $fallback
            : '';
    }

    private function isSafeLandingPath(string $path): bool
    {
        return preg_match(
            '#^/images/landing/(?:stock/[A-Za-z0-9._-]+|uploads/[A-Za-z0-9._-]+|[A-Za-z0-9._-]+)$#D',
            $path
        ) === 1;
    }

    private function imageFileExists(string $path): bool
    {
        if (str_starts_with($path, (string) config('zazu.landing_media_uploads.url_prefix', '/images/landing/uploads').'/')) {
            $directory = (string) config('zazu.landing_media_uploads.directory', public_path('images/landing/uploads'));
            return is_file(rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.basename($path));
        }

        return is_file(public_path(ltrim($path, '/')));
    }

    /**
     * @param array<string, string> $selected
     */
    public function save(array $selected): void
    {
        $library = $this->library();
        $settings = PlatformLandingSetting::current();
        $paths = [];

        foreach (self::SLOT_DEFAULTS as $field => $definition) {
            $key = $selected[$field] ?? '';
            if (!isset($library[$key]['url'])) {
                throw new RuntimeException('The selected landing image is no longer available.');
            }

            $paths[$definition['column']] = $library[$key]['url'];
        }

        $settings->fill($paths)->save();
    }

    /**
     * Store validated raster uploads as public, opaque filenames.
     *
     * @param array<int, UploadedFile> $files
     */
    public function storeUploads(array $files): int
    {
        if (!Schema::hasTable('platform_landing_media')) {
            throw new RuntimeException('The landing media library is not available until its migration has run.');
        }

        $directory = (string) config('zazu.landing_media_uploads.directory', public_path('images/landing/uploads'));
        $urlPrefix = rtrim((string) config('zazu.landing_media_uploads.url_prefix', '/images/landing/uploads'), '/');
        File::ensureDirectoryExists($directory);

        $stored = 0;
        foreach ($files as $file) {
            $mimeType = (string) $file->getMimeType();
            if (!isset(self::IMAGE_MIME_EXTENSIONS[$mimeType])) {
                throw new RuntimeException('Only JPEG, PNG and WebP images can be uploaded.');
            }

            $filename = Str::uuid()->toString().'.'.self::IMAGE_MIME_EXTENSIONS[$mimeType];
            $label = $this->uploadLabel($file);
            $fileSize = (int) ($file->getSize() ?? 0);
            $file->move($directory, $filename);
            $path = $urlPrefix.'/'.$filename;

            try {
                PlatformLandingMedia::query()->create([
                    'label' => $label,
                    'path' => $path,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                ]);
            } catch (Throwable $exception) {
                File::delete(rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename);
                throw $exception;
            }

            $stored++;
        }

        return $stored;
    }

    private function uploadLabel(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $label = trim((string) Str::of($name)->replace(['_', '-'], ' ')->replaceMatches('/\s+/', ' ')->limit(80, ''));

        return $label !== '' ? $label : 'Uploaded landing image';
    }
}

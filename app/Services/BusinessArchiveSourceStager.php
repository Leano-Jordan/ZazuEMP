<?php

namespace App\Services;

use RuntimeException;
use Throwable;

/**
 * Copies a storage stream into a restricted temporary source for ZIP creation.
 */
final class BusinessArchiveSourceStager
{
    private const STREAM_CHUNK_BYTES = 1048576;

    /**
     * @param  resource  $source
     * @return array{0:string,1:int}
     */
    public function stage($source, int $remainingBytes): array
    {
        $path = $this->createStagingFile();
        $target = @fopen($path, 'wb');

        if ($target === false) {
            @unlink($path);

            throw new RuntimeException('A private business-file staging file could not be opened.');
        }

        try {
            $bytes = $this->copyStream($source, $target, $remainingBytes);
            if (! fflush($target)) {
                throw new RuntimeException('A staged business file could not be finalized.');
            }
        } catch (Throwable $exception) {
            fclose($target);
            @unlink($path);

            throw $exception;
        }

        fclose($target);

        return [$path, $bytes];
    }

    private function createStagingFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-export-source-');
        if ($path === false) {
            throw new RuntimeException('A private business-file staging file could not be created.');
        }

        if (! @chmod($path, 0600)) {
            @unlink($path);

            throw new RuntimeException('A private business-file staging file could not be secured.');
        }

        return $path;
    }

    /**
     * @param  resource  $source
     * @param  resource  $target
     */
    private function copyStream($source, $target, int $remainingBytes): int
    {
        $bytes = 0;

        while (! feof($source)) {
            $chunk = $this->readChunk($source);
            if ($chunk === null) {
                break;
            }

            $chunkBytes = strlen($chunk);
            $this->assertWithinLimit($bytes, $chunkBytes, $remainingBytes);
            $this->writeAll($target, $chunk);
            $bytes += $chunkBytes;
        }

        return $bytes;
    }

    /**
     * @param  resource  $source
     */
    private function readChunk($source): ?string
    {
        $chunk = fread($source, self::STREAM_CHUNK_BYTES);
        if ($chunk === false) {
            throw new RuntimeException('A business file payload could not be read from storage.');
        }

        if ($chunk === '' && ! feof($source)) {
            throw new RuntimeException('A business file storage stream ended unexpectedly.');
        }

        return $chunk === '' ? null : $chunk;
    }

    private function assertWithinLimit(int $bytes, int $chunkBytes, int $remainingBytes): void
    {
        if ($chunkBytes > $remainingBytes - $bytes) {
            throw new RuntimeException('Business files exceed the 250 MB archive export limit.');
        }
    }

    /**
     * @param  resource  $target
     */
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
}

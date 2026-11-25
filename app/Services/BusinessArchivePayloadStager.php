<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Stages one untrusted ZIP payload into an owner-only temporary file.
 */
final class BusinessArchivePayloadStager
{
    private const STREAM_CHUNK_BYTES = 1048576;

    /**
     * @return array{staged_path:string,payload_bytes:int}
     */
    public function stage(ZipArchive $zip, string $entry, int $remainingBytes): array
    {
        $source = $zip->getStream($entry);
        if ($source === false) {
            throw new RuntimeException('A referenced business file is missing from the archive.');
        }

        $path = null;
        $target = false;

        try {
            $path = $this->createPrivateTemporaryFile();
            $target = @fopen($path, 'wb');
            if ($target === false) {
                throw new RuntimeException('A private business-file staging file could not be opened.');
            }

            $bytes = $this->copyStream($source, $target, $remainingBytes);
            if (! fflush($target)) {
                throw new RuntimeException('A staged business file could not be finalized.');
            }

            return ['staged_path' => $path, 'payload_bytes' => $bytes];
        } catch (\Throwable $exception) {
            if (is_string($path)) {
                @unlink($path);
            }

            throw $exception;
        } finally {
            fclose($source);
            if (is_resource($target)) {
                fclose($target);
            }
        }
    }

    private function createPrivateTemporaryFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-restore-file-');
        if ($path === false || ! @chmod($path, 0600)) {
            if (is_string($path)) {
                @unlink($path);
            }

            throw new RuntimeException('A private business-file staging file could not be created.');
        }

        return $path;
    }

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

    private function readChunk($source): ?string
    {
        $chunk = fread($source, self::STREAM_CHUNK_BYTES);
        if ($chunk === false) {
            throw new RuntimeException('A business file payload could not be read from the archive.');
        }
        if ($chunk === '' && ! feof($source)) {
            throw new RuntimeException('A business file payload stream ended unexpectedly.');
        }

        return $chunk === '' ? null : $chunk;
    }

    private function assertWithinLimit(int $bytes, int $chunkBytes, int $remainingBytes): void
    {
        if ($chunkBytes > $remainingBytes - $bytes) {
            throw new RuntimeException('The business archive exceeds the 250 MB restore limit.');
        }
    }

    /** @param resource $stream */
    private function writeAll($stream, string $contents): void
    {
        $offset = 0;
        $length = strlen($contents);
        while ($offset < $length) {
            $written = fwrite($stream, substr($contents, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('A staged business file could not be written.');
            }
            $offset += $written;
        }
    }
}

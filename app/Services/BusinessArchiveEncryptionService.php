<?php

namespace App\Services;

use RuntimeException;

/**
 * Streams authenticated business-archive encryption without loading the archive
 * into memory. Installation-wide scheduled backups deliberately do not use it.
 */
final class BusinessArchiveEncryptionService
{
    public function __construct(private readonly BusinessArchiveEncryptionStream $stream)
    {
    }

    public function isEncrypted(string $path): bool
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('The uploaded business archive could not be opened for format detection.');
        }

        try {
            $magic = fread($handle, strlen(BusinessArchiveEncryptionStream::MAGIC));
            if ($magic === false) {
                throw new RuntimeException('The uploaded business archive format header could not be read.');
            }

            return $magic === BusinessArchiveEncryptionStream::MAGIC;
        } finally {
            fclose($handle);
        }
    }

    public function encryptFile(string $sourcePath, #[\SensitiveParameter] string $passphrase): string
    {
        $this->stream->assertAvailable();
        $this->assertPassphrase($passphrase);

        $source = @fopen($sourcePath, 'rb');
        if ($source === false) {
            throw new RuntimeException('The business archive could not be read for encryption.');
        }

        try {
            $targetPath = $this->privateTemporaryFile();
        } catch (\Throwable $exception) {
            fclose($source);
            throw $exception;
        }

        $target = @fopen($targetPath, 'wb');
        if ($target === false) {
            fclose($source);
            @unlink($targetPath);
            throw new RuntimeException('A private encrypted archive could not be created.');
        }

        try {
            $this->stream->encrypt($source, $target, $passphrase);
        } catch (\Throwable $exception) {
            if (is_resource($target)) {
                fclose($target);
            }
            @unlink($targetPath);

            throw $exception instanceof RuntimeException
                ? $exception
                : new RuntimeException('The business archive could not be encrypted.', 0, $exception);
        } finally {
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($target)) {
                fclose($target);
            }
        }

        return $targetPath;
    }

    public function decryptFile(string $sourcePath, #[\SensitiveParameter] string $passphrase): string
    {
        $this->stream->assertAvailable();
        $this->assertPassphrase($passphrase);

        $source = @fopen($sourcePath, 'rb');
        if ($source === false) {
            throw new RuntimeException('The uploaded business archive could not be read.');
        }

        try {
            $targetPath = $this->privateTemporaryFile();
        } catch (\Throwable $exception) {
            fclose($source);
            throw $exception;
        }

        $target = @fopen($targetPath, 'wb');
        if ($target === false) {
            fclose($source);
            @unlink($targetPath);
            throw new RuntimeException('A private restore staging file could not be created.');
        }

        try {
            $this->stream->decrypt($source, $target, $passphrase);
        } catch (\Throwable $exception) {
            if (is_resource($target)) {
                fclose($target);
            }
            @unlink($targetPath);

            throw $exception instanceof RuntimeException
                ? $exception
                : new RuntimeException('The archive password is incorrect or the encrypted archive is damaged.', 0, $exception);
        } finally {
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($target)) {
                fclose($target);
            }
        }

        return $targetPath;
    }

    private function assertPassphrase(string $passphrase): void
    {
        $characters = preg_match_all('/./us', $passphrase);
        if ($characters === false || $characters < 12 || $characters > 128 || strlen($passphrase) > 512) {
            throw new RuntimeException('Use a business archive passphrase between 12 and 128 characters.');
        }
    }

    private function privateTemporaryFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-business-archive-');
        if ($path === false || ! @chmod($path, 0600)) {
            if (is_string($path)) {
                @unlink($path);
            }
            throw new RuntimeException('A private temporary archive file could not be created.');
        }

        return $path;
    }

}

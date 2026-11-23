<?php

namespace App\Services;

use RuntimeException;

/**
 * Streams authenticated business-archive encryption without loading the archive
 * into memory. Installation-wide scheduled backups deliberately do not use it.
 */
final class BusinessArchiveEncryptionService
{
    private const MAGIC = 'ZAZUENC2';
    private const CHUNK_BYTES = 1048576;

    public function isEncrypted(string $path): bool
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        try {
            return fread($handle, strlen(self::MAGIC)) === self::MAGIC;
        } finally {
            fclose($handle);
        }
    }

    public function encryptFile(string $sourcePath, #[\SensitiveParameter] string $passphrase): string
    {
        $this->assertAvailable();
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
            $this->writeEncryptedStream($source, $target, $passphrase);
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
        $this->assertAvailable();
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
            $this->readDecryptedStream($source, $target, $passphrase);
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

    private function writeEncryptedStream($source, $target, string $passphrase): void
    {
        $salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
        $key = null;
        $state = null;

        try {
            $key = sodium_crypto_pwhash(
                SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES,
                $passphrase,
                $salt,
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
            );
            [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);
            $this->writeAll($target, self::MAGIC.$salt.$header);
            $this->writeEncryptedChunks($source, $target, $state);

            if (! fflush($target)) {
                throw new RuntimeException('The encrypted business archive could not be finalized.');
            }
        } finally {
            if (is_string($state)) {
                sodium_memzero($state);
            }
            if (is_string($key)) {
                sodium_memzero($key);
            }
        }
    }

    private function writeEncryptedChunks($source, $target, string &$state): void
    {
        $buffer = fread($source, self::CHUNK_BYTES);
        if ($buffer === false) {
            throw new RuntimeException('The business archive could not be read for encryption.');
        }

        while (true) {
            $next = fread($source, self::CHUNK_BYTES);
            if ($next === false) {
                throw new RuntimeException('The business archive could not be read for encryption.');
            }

            $isFinal = $next === '';
            $ciphertext = sodium_crypto_secretstream_xchacha20poly1305_push(
                $state,
                $buffer,
                '',
                $isFinal
                    ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL
                    : SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE
            );

            $this->writeAll($target, pack('N', strlen($ciphertext)).$ciphertext);
            if ($isFinal) {
                return;
            }
            $buffer = $next;
        }
    }

    private function readDecryptedStream($source, $target, string $passphrase): void
    {
        $prefix = $this->readExact($source, strlen(self::MAGIC) + SODIUM_CRYPTO_PWHASH_SALTBYTES
            + SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES);
        if (substr($prefix, 0, strlen(self::MAGIC)) !== self::MAGIC) {
            throw new RuntimeException('The uploaded file is not an encrypted Zazu business archive.');
        }

        $saltOffset = strlen(self::MAGIC);
        $salt = substr($prefix, $saltOffset, SODIUM_CRYPTO_PWHASH_SALTBYTES);
        $streamHeader = substr($prefix, $saltOffset + SODIUM_CRYPTO_PWHASH_SALTBYTES);
        $key = null;
        $state = null;

        try {
            $key = sodium_crypto_pwhash(
                SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES,
                $passphrase,
                $salt,
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
            );
            $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($streamHeader, $key);
            $this->readDecryptedChunks($source, $target, $state);

            if (fread($source, 1) !== '' || ! fflush($target)) {
                throw new RuntimeException('The encrypted business archive contains trailing data or could not be finalized.');
            }
        } finally {
            if (is_string($state)) {
                sodium_memzero($state);
            }
            if (is_string($key)) {
                sodium_memzero($key);
            }
        }
    }

    private function readDecryptedChunks($source, $target, string &$state): void
    {
        $finalSeen = false;
        while (! $finalSeen) {
            $lengthBytes = fread($source, 4);
            if ($lengthBytes === false || strlen($lengthBytes) !== 4) {
                throw new RuntimeException('The archive password is incorrect or the encrypted archive is damaged.');
            }

            $length = unpack('Nlength', $lengthBytes)['length'];
            if ($length < SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES
                || $length > self::CHUNK_BYTES + SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES) {
                throw new RuntimeException('The encrypted business archive has an invalid chunk length.');
            }

            $ciphertext = $this->readExact($source, $length);
            $opened = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $ciphertext);
            if ($opened === false) {
                throw new RuntimeException('The archive password is incorrect or the encrypted archive is damaged.');
            }

            [$plaintext, $tag] = $opened;
            if (! in_array($tag, [
                SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE,
                SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL,
            ], true)) {
                throw new RuntimeException('The encrypted business archive contains an unsupported stream tag.');
            }

            $this->writeAll($target, $plaintext);
            $finalSeen = $tag === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL;
        }
    }

    private function assertAvailable(): void
    {
        foreach ([
            'sodium_crypto_secretstream_xchacha20poly1305_init_push',
            'sodium_crypto_secretstream_xchacha20poly1305_init_pull',
            'sodium_crypto_secretstream_xchacha20poly1305_push',
            'sodium_crypto_secretstream_xchacha20poly1305_pull',
            'sodium_crypto_pwhash',
            'sodium_memzero',
        ] as $function) {
            if (! function_exists($function)) {
                throw new RuntimeException('PHP Sodium is required for encrypted business archives. Update or enable the PHP Sodium extension.');
            }
        }
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

    /** @param resource $stream */
    private function readExact($stream, int $length): string
    {
        $contents = '';
        while (strlen($contents) < $length && ! feof($stream)) {
            $chunk = fread($stream, $length - strlen($contents));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $contents .= $chunk;
        }

        if (strlen($contents) !== $length) {
            throw new RuntimeException('The encrypted business archive is truncated.');
        }

        return $contents;
    }

    /** @param resource $stream */
    private function writeAll($stream, string $contents): void
    {
        $offset = 0;
        $length = strlen($contents);
        while ($offset < $length) {
            $written = fwrite($stream, substr($contents, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('The encrypted business archive could not be written.');
            }
            $offset += $written;
        }
    }
}

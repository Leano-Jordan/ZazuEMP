<?php

namespace App\Support;

final class ZazuLocalSentinelRecovery
{
    private readonly string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? storage_path('framework/sentinel-recovery.json');
    }

    /**
     * Issue a one-use, five-minute code for a person operating the local host.
     */
    public function issue(): string
    {
        $directory = dirname($this->path);

        if (! is_dir($directory) && ! @mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new \RuntimeException('The local recovery directory could not be created.');
        }

        return $this->withLock(function (): string {
            $code = bin2hex(random_bytes(32));
            $payload = json_encode([
                'token_hash' => hash('sha256', $code),
                'expires_at' => time() + 300,
            ], JSON_THROW_ON_ERROR);

            if (file_put_contents($this->path, $payload, LOCK_EX) === false) {
                throw new \RuntimeException('The local recovery challenge could not be stored.');
            }

            @chmod($this->path, 0600);

            return $code;
        });
    }

    /**
     * Consume the code atomically. A valid code can succeed only once.
     */
    public function consume(string $code): bool
    {
        if (preg_match('/\A[a-f0-9]{64}\z/D', $code) !== 1 || ! is_file($this->path)) {
            return false;
        }

        return $this->withLock(function () use ($code): bool {
            if (! is_file($this->path) || ! is_readable($this->path)) {
                return false;
            }

            $contents = file_get_contents($this->path);

            if (! is_string($contents)) {
                return false;
            }

            try {
                $challenge = json_decode($contents, true, 8, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return false;
            }

            $expectedHash = $challenge['token_hash'] ?? null;
            $expiresAt = $challenge['expires_at'] ?? null;

            if (
                ! is_string($expectedHash)
                || ! is_int($expiresAt)
                || $expiresAt < time()
                || ! hash_equals($expectedHash, hash('sha256', $code))
            ) {
                return false;
            }

            // Invalidate while holding the lock so concurrent requests cannot replay it.
            if (@unlink($this->path)) {
                return true;
            }

            $invalidated = json_encode([
                'token_hash' => hash('sha256', random_bytes(32)),
                'expires_at' => 0,
            ], JSON_THROW_ON_ERROR);

            return file_put_contents($this->path, $invalidated, LOCK_EX) !== false;
        });
    }

    private function withLock(callable $callback): mixed
    {
        $lockPath = $this->path.'.lock';
        $handle = @fopen($lockPath, 'c');

        if (! is_resource($handle)) {
            throw new \RuntimeException('The local recovery lock could not be opened.');
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new \RuntimeException('The local recovery lock could not be acquired.');
            }

            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}

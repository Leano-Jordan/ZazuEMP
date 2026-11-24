<?php

namespace App\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Runs database client processes without buffering complete dumps in PHP memory.
 */
final class ProcessFileStream
{
    private const MAX_DIAGNOSTIC_BYTES = 8192;

    public static function runToFile(Process $process, string $path, string $failureMessage): void
    {
        $output = fopen($path, 'wb');
        if ($output === false) {
            throw new RuntimeException('Could not open a private database dump file for writing.');
        }

        $diagnostic = '';

        try {
            $process->run(self::outputCallback($process, $output, $diagnostic));
            self::assertSuccessfulProcess($process, $failureMessage, $diagnostic);

            if (!fflush($output)) {
                throw new RuntimeException('Could not flush the completed database dump to disk.');
            }
        } catch (\Throwable $exception) {
            self::removeFailedOutput($output, $path, $exception);
        }

        fclose($output);
        self::assertNonEmptyOutput($path, $failureMessage);
    }

    private static function outputCallback(Process $process, $output, string &$diagnostic): \Closure
    {
        return static function (string $type, string $chunk) use ($process, $output, &$diagnostic): void {
            if ($type === Process::OUT) {
                self::writeAll($output, $chunk);
                $process->clearOutput();

                return;
            }

            $diagnostic = self::appendTail($diagnostic, $chunk);
            $process->clearErrorOutput();
        };
    }

    private static function assertSuccessfulProcess(Process $process, string $failureMessage, string $diagnostic): void
    {
        if (!$process->isSuccessful()) {
            throw new RuntimeException($failureMessage.($diagnostic !== '' ? ' '.$diagnostic : ''));
        }
    }

    private static function removeFailedOutput($output, string $path, \Throwable $exception): never
    {
        fclose($output);

        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException($exception->getMessage().' The incomplete dump could not be removed.', 0, $exception);
        }

        throw $exception;
    }

    private static function assertNonEmptyOutput(string $path, string $failureMessage): void
    {
        if (is_file($path) && filesize($path) > 0) {
            return;
        }

        if (is_file($path)) {
            unlink($path);
        }

        throw new RuntimeException($failureMessage.' The database client returned an empty dump.');
    }

    public static function runFromFile(Process $process, string $path, string $failureMessage): string
    {
        if (!is_file($path) || !is_readable($path) || filesize($path) === 0) {
            throw new RuntimeException('Database import file is empty or unavailable.');
        }

        $input = fopen($path, 'rb');
        if ($input === false) {
            throw new RuntimeException('Could not open the database import file.');
        }

        $diagnostic = '';

        try {
            $process->setInput($input);
            $process->run(static function (string $type, string $chunk) use ($process, &$diagnostic): void {
                if ($type === Process::ERR) {
                    $diagnostic = self::appendTail($diagnostic, $chunk);
                    $process->clearErrorOutput();

                    return;
                }

                $process->clearOutput();
            });
        } finally {
            fclose($input);
        }

        if (!$process->isSuccessful()) {
            throw new RuntimeException($failureMessage.($diagnostic !== '' ? ' '.$diagnostic : ''));
        }

        return $diagnostic;
    }

    /** @param resource $stream */
    private static function writeAll($stream, string $chunk): void
    {
        $length = strlen($chunk);
        $offset = 0;

        while ($offset < $length) {
            $written = fwrite($stream, substr($chunk, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Could not write the database dump stream to disk.');
            }

            $offset += $written;
        }
    }

    private static function appendTail(string $current, string $chunk): string
    {
        $combined = $current.$chunk;

        return strlen($combined) > self::MAX_DIAGNOSTIC_BYTES
            ? substr($combined, -self::MAX_DIAGNOSTIC_BYTES)
            : $combined;
    }
}

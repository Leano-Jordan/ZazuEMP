<?php

namespace App\Support;

use DateTimeImmutable;
use Throwable;

final class ZazuBackupStatus
{
    private const MAX_AGE_HOURS = 26;

    public function __construct(private readonly ?string $directory = null)
    {
    }

    /**
     * Report local archive freshness and whether an independent copy was recently verified.
     * This does not imply that a restore drill passed.
     *
     * @return array{
     *   status:string,
     *   latest_at:?string,
     *   age_hours:?int,
     *   message:string,
     *   secondary_status:string,
     *   secondary_at:?string,
     *   secondary_age_hours:?int,
     *   secondary_message:string
     * }
     */
    public function inspect(): array
    {
        $directory = $this->directory ?? storage_path('app/zazu-backups/daily');

        return $this->combineBackupStatus(
            $this->localArchiveStatus($directory),
            $this->secondaryCopyStatus($directory),
        );
    }

    /**
     * @param array{status:string,latest_at:?string,age_hours:?int,message:string} $local
     * @param array{secondary_status:string,secondary_at:?string,secondary_age_hours:?int,secondary_message:string} $secondary
     * @return array<string, mixed>
     */
    private function combineBackupStatus(array $local, array $secondary): array
    {
        return [
            'status' => $this->isBackupProtectionHealthy($local, $secondary) ? 'healthy' : 'attention',
            'latest_at' => $local['latest_at'],
            'age_hours' => $local['age_hours'],
            'message' => $local['message'],
            ...$secondary,
        ];
    }

    /**
     * @param array{status:string} $local
     * @param array{secondary_status:string} $secondary
     */
    private function isBackupProtectionHealthy(array $local, array $secondary): bool
    {
        return $local['status'] === 'healthy' && $secondary['secondary_status'] === 'healthy';
    }

    /**
     * @return array{status:string,latest_at:?string,age_hours:?int,message:string}
     */
    private function localArchiveStatus(string $directory): array
    {
        $archives = $this->archivePaths($directory);
        if ($archives === []) {
            return [
                'status' => 'attention',
                'latest_at' => null,
                'age_hours' => null,
                'message' => 'No scheduled backup has been recorded on this PC for this installation.',
            ];
        }

        $timestamp = filemtime($archives[0]);
        $ageHours = (int) floor(max(0, now()->timestamp - $timestamp) / 3600);
        $fresh = $ageHours <= self::MAX_AGE_HOURS;

        return [
            'status' => $fresh ? 'healthy' : 'attention',
            'latest_at' => date('Y-m-d H:i:s T', $timestamp),
            'age_hours' => $ageHours,
            'message' => $fresh
                ? 'A recent on-PC installation backup archive is present.'
                : 'The latest on-PC installation backup is overdue.',
        ];
    }

    /**
     * @return list<string>
     */
    private function archivePaths(string $directory): array
    {
        $archives = glob($directory.DIRECTORY_SEPARATOR.'zazu-backup-*.zip') ?: [];
        $archives = array_values(array_filter(
            $archives,
            static fn (string $path): bool => is_file($path) && ! is_link($path) && (filesize($path) ?: 0) > 0
        ));

        usort($archives, static fn (string $left, string $right): int => filemtime($right) <=> filemtime($left));

        return $archives;
    }

    /**
     * @return array{secondary_status:string,secondary_at:?string,secondary_age_hours:?int,secondary_message:string}
     */
    private function secondaryCopyStatus(string $directory): array
    {
        $state = $this->readSecondaryState($directory);
        $lastSuccessfulAt = $this->lastSuccessfulTimestamp($state);
        $ageHours = $this->ageHours($lastSuccessfulAt);
        $verified = $this->isVerifiedSecondaryCopy($state, $ageHours);

        return [
            'secondary_status' => $verified ? 'healthy' : 'attention',
            'secondary_at' => $lastSuccessfulAt,
            'secondary_age_hours' => $ageHours,
            'secondary_message' => $this->secondaryMessage($state, $verified, $ageHours),
        ];
    }

    /**
     * @param array<string, mixed> $state
     */
    private function lastSuccessfulTimestamp(array $state): ?string
    {
        $timestamp = $state['last_successful_at'] ?? null;

        return is_string($timestamp) ? $timestamp : null;
    }

    /**
     * @param array<string, mixed> $state
     */
    private function isVerifiedSecondaryCopy(array $state, ?int $ageHours): bool
    {
        return ($state['status'] ?? null) === 'verified'
            && $ageHours !== null
            && $ageHours <= self::MAX_AGE_HOURS
            && (new SecondaryBackupArchiveVerifier())->inspect($state) === 'present';
    }

    /**
     * @return array<string, mixed>
     */
    private function readSecondaryState(string $directory): array
    {
        $statusPath = $directory.DIRECTORY_SEPARATOR.'.secondary-copy-status.json';
        if (! is_file($statusPath) || is_link($statusPath) || (filesize($statusPath) ?: 0) > 16384) {
            return [];
        }

        try {
            $decoded = json_decode((string) file_get_contents($statusPath), true, 16, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (Throwable) {
            return [];
        }
    }

    private function ageHours(?string $timestamp): ?int
    {
        if ($timestamp === null) {
            return null;
        }

        try {
            return (int) floor(max(
                0,
                now()->timestamp - (new DateTimeImmutable($timestamp))->getTimestamp()
            ) / 3600);
        } catch (Throwable) {
            return null;
        }
    }

    private function secondaryMessage(array $state, bool $verified, ?int $ageHours): string
    {
        if ($verified) {
            return 'An independent backup copy is present and its recorded size and timestamp match; SHA-256 was verified when copied '.$ageHours.' hours ago.';
        }

        if (($state['status'] ?? null) === 'verified') {
            return match ((new SecondaryBackupArchiveVerifier())->inspect($state)) {
                'missing' => 'The last verified independent backup archive is missing or its drive is unavailable.',
                'changed' => 'The last verified independent backup archive changed after verification. Run a new backup.',
                'invalid' => 'The last verified independent backup archive has no valid integrity record. Run a new backup.',
                default => 'The last independent backup copy is overdue. Check that its destination is available.',
            };
        }

        return match ($state['status'] ?? null) {
            'failed' => 'The independent backup copy failed on the last scheduled run. Check the USB drive or synced-folder destination.',
            'not_configured' => 'No independent backup destination is configured. The only archive copy remains on this PC.',
            default => 'No independent backup destination is configured; no independent backup copy has been verified on this installation.',
        };
    }
}

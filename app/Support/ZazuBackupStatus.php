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
        $secondary = $this->secondaryCopyStatus($directory);
        $archives = glob($directory.DIRECTORY_SEPARATOR.'zazu-backup-*.zip') ?: [];
        $archives = array_values(array_filter(
            $archives,
            static fn (string $path): bool => is_file($path) && ! is_link($path) && (filesize($path) ?: 0) > 0
        ));

        usort($archives, static fn (string $left, string $right): int => filemtime($right) <=> filemtime($left));

        if ($archives === []) {
            return [
                'status' => 'attention',
                'latest_at' => null,
                'age_hours' => null,
                'message' => 'No scheduled installation backup has been recorded on this PC.',
                ...$secondary,
            ];
        }

        $timestamp = filemtime($archives[0]);
        $ageHours = (int) floor(max(0, now()->timestamp - $timestamp) / 3600);
        $localFresh = $ageHours <= self::MAX_AGE_HOURS;
        $secondaryFresh = $secondary['secondary_status'] === 'healthy';
        $status = $localFresh && $secondaryFresh ? 'healthy' : 'attention';

        $message = ! $localFresh
            ? 'The latest on-PC installation backup is overdue.'
            : ($secondaryFresh
                ? 'Recent on-PC and independent backup copies have been verified.'
                : $secondary['secondary_message']);

        return [
            'status' => $status,
            'latest_at' => date('Y-m-d H:i:s T', $timestamp),
            'age_hours' => $ageHours,
            'message' => $message,
            ...$secondary,
        ];
    }

    /**
     * @return array{secondary_status:string,secondary_at:?string,secondary_age_hours:?int,secondary_message:string}
     */
    private function secondaryCopyStatus(string $directory): array
    {
        $statusPath = $directory.DIRECTORY_SEPARATOR.'.secondary-copy-status.json';
        $state = null;

        if (is_file($statusPath) && ! is_link($statusPath) && (filesize($statusPath) ?: 0) <= 16384) {
            try {
                $decoded = json_decode((string) file_get_contents($statusPath), true, 16, JSON_THROW_ON_ERROR);
                $state = is_array($decoded) ? $decoded : null;
            } catch (Throwable) {
                $state = null;
            }
        }

        $lastSuccessfulAt = is_string($state['last_successful_at'] ?? null)
            ? $state['last_successful_at']
            : null;
        $ageHours = null;

        if ($lastSuccessfulAt !== null) {
            try {
                $ageHours = (int) floor(max(
                    0,
                    now()->timestamp - (new DateTimeImmutable($lastSuccessfulAt))->getTimestamp()
                ) / 3600);
            } catch (Throwable) {
                $ageHours = null;
            }
        }

        $verified = ($state['status'] ?? null) === 'verified'
            && $ageHours !== null
            && $ageHours <= self::MAX_AGE_HOURS;

        $message = $verified
            ? 'An independent backup copy was verified '.$ageHours.' hours ago.'
            : match ($state['status'] ?? null) {
                'failed' => 'The independent backup copy failed on the last scheduled run. Check the USB drive or synced-folder destination.',
                'not_configured' => 'No independent backup destination is configured. The only archive copy remains on this PC.',
                'verified' => 'The last independent backup copy is overdue. Check that its destination is available.',
                default => 'No independent backup copy has been verified on this installation.',
            };

        return [
            'secondary_status' => $verified ? 'healthy' : 'attention',
            'secondary_at' => $lastSuccessfulAt,
            'secondary_age_hours' => $ageHours,
            'secondary_message' => $message,
        ];
    }
}

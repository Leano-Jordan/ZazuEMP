<?php

namespace App\Support;

final class ZazuBackupStatus
{
    public function __construct(private readonly ?string $directory = null)
    {
    }

    /**
     * Report the newest completed archive without implying that a restore drill passed.
     *
     * @return array{status:string,latest_at:?string,age_hours:?int,message:string}
     */
    public function inspect(): array
    {
        $directory = $this->directory ?? storage_path('app/zazu-backups/daily');
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
                'message' => 'No scheduled backup has been recorded on this installation.',
            ];
        }

        $timestamp = filemtime($archives[0]);
        $ageHours = (int) floor(max(0, now()->timestamp - $timestamp) / 3600);
        $status = $ageHours <= 26 ? 'healthy' : 'attention';

        return [
            'status' => $status,
            'latest_at' => date('Y-m-d H:i:s T', $timestamp),
            'age_hours' => $ageHours,
            'message' => $status === 'healthy'
                ? 'A recent non-empty installation backup archive is present.'
                : 'The latest installation backup is overdue.',
        ];
    }
}

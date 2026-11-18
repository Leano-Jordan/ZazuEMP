<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class ZazuDatabaseHealth
{
    public function inspect(): array
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $connection->select('select 1 as sentinel_probe');
        } catch (Throwable $exception) {
            return [
                $this->failedConnectionCheck($exception),
                $this->notChecked('migration_history', 'Migration history', 'Not checked because the database connection failed.'),
            ];
        }

        return [
            [
                'key' => 'database_connection',
                'group' => 'Database',
                'label' => 'Database connection',
                'status' => 'healthy',
                'message' => 'The configured database accepted a read-only query.',
                'driver' => $driver,
            ],
            $this->migrationCheck($connection),
        ];
    }

    private function failedConnectionCheck(Throwable $exception): array
    {
        $incident = $this->recordFailure($exception);
        $failureClass = $incident['database_failure_class'] ?? null;
        $errorCode = $incident['error_code'] ?? ZazuErrorCatalog::for($exception)['code'];

        return [
            'key' => 'database_connection',
            'group' => 'Database',
            'label' => 'Database connection',
            'status' => 'failed',
            'message' => $this->explainFailure($failureClass),
            'error_code' => $errorCode,
            'failure_class' => $failureClass,
            'reference' => $incident['request_id'] ?? null,
            'driver' => $incident['database_driver'] ?? $this->configuredDriver(),
        ];
    }

    private function migrationCheck(Connection $connection): array
    {
        try {
            $schema = $connection->getSchemaBuilder();

            if (! $schema->hasTable('migrations')) {
                return [
                    'key' => 'migration_history',
                    'group' => 'Database',
                    'label' => 'Migration history',
                    'status' => 'failed',
                    'message' => 'The migration history table is missing. This installation may not have completed its database setup.',
                ];
            }

            $applied = array_map('strval', $connection->table('migrations')->pluck('migration')->all());
            $files = glob(database_path('migrations'.DIRECTORY_SEPARATOR.'*.php')) ?: [];

            if ($files === []) {
                return [
                    'key' => 'migration_history',
                    'group' => 'Database',
                    'label' => 'Migration history',
                    'status' => 'failed',
                    'message' => 'No application migration files were found in this installation.',
                ];
            }

            $expected = array_map(
                static fn (string $file): string => pathinfo($file, PATHINFO_FILENAME),
                $files,
            );
            $pending = array_values(array_diff($expected, $applied));
        } catch (Throwable $exception) {
            $incident = $this->recordFailure($exception);

            return [
                'key' => 'migration_history',
                'group' => 'Database',
                'label' => 'Migration history',
                'status' => 'failed',
                'message' => 'The database answered the connection probe, but Sentinel could not read migration history.',
                'error_code' => $incident['error_code'] ?? ZazuErrorCatalog::for($exception)['code'],
                'reference' => $incident['request_id'] ?? null,
            ];
        }

        $pendingCount = count($pending);

        return [
            'key' => 'migration_history',
            'group' => 'Database',
            'label' => 'Migration history',
            'status' => $pendingCount > 0 ? 'attention' : 'healthy',
            'message' => $pendingCount > 0
                ? number_format($pendingCount).' application migration(s) are not recorded as applied. The installed schema may be behind this Zazu build.'
                : 'Every migration file in this installation is recorded as applied.',
            'pending_count' => $pendingCount,
            'pending_migrations' => array_slice($pending, 0, 10),
            'next_step' => $pendingCount > 0
                ? 'Do not apply changes from this screen. Create and verify a full database and private-files backup before any migration operation.'
                : null,
        ];
    }

    private function recordFailure(Throwable $exception): array
    {
        try {
            ZazuIncidentRecorder::record($exception);
        } catch (Throwable) {
            // Sentinel must still render its safe health result if capture or logging also fails.
        }

        $requestId = request()->attributes->get('zazu_request_id');

        if (! is_string($requestId) || ! Str::isUuid($requestId)) {
            return [];
        }

        return ZazuIncidentLedger::find($requestId) ?? [];
    }

    private function configuredDriver(): ?string
    {
        $name = config('database.default');
        $driver = is_string($name) ? config('database.connections.'.$name.'.driver') : null;

        return is_string($driver) && preg_match('/^[a-z0-9_]{1,32}$/i', $driver) === 1
            ? $driver
            : null;
    }

    private function explainFailure(?string $failureClass): string
    {
        return match ($failureClass) {
            'sqlite_missing_table', 'database_missing_table' => 'A required database table is missing. The installed schema may not match this Zazu build.',
            'sqlite_missing_column', 'database_missing_column' => 'A required database column is missing. The installed schema may not match this Zazu build.',
            'sqlite_locked', 'sqlite_schema_locked' => 'SQLite is locked by another operation. Avoid closing processes or replacing the database while it may be in use.',
            'sqlite_read_only' => 'The database or its containing folder is read-only. Check Windows file and folder permissions.',
            'sqlite_disk_full' => 'The storage volume is full. Free space before attempting more database writes.',
            'sqlite_disk_io' => 'Windows reported a disk read/write problem. Preserve the database and check the storage device before attempting repairs.',
            'sqlite_malformed' => 'SQLite reported malformed database contents. Stop writes and preserve a copy before any repair or restore attempt.',
            'sqlite_open_failed' => 'The SQLite database file could not be opened. Its file or containing folder may be missing or inaccessible.',
            'sqlite_busy' => 'SQLite is busy with another operation. Wait for the active operation to finish before retrying.',
            'database_connection_failure' => 'The database connection could not be established. Check the local database service and configured connection.',
            default => 'The database did not answer a read-only probe. Use the reference report for safe technical details; the original error message is deliberately hidden.',
        };
    }

    private function notChecked(string $key, string $label, string $message): array
    {
        return [
            'key' => $key,
            'group' => 'Database',
            'label' => $label,
            'status' => 'not_checked',
            'message' => $message,
        ];
    }
}

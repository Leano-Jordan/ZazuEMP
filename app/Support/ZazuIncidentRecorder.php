<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class ZazuIncidentRecorder
{
    public static function record(Throwable $exception): void
    {
        try {
            $definition = ZazuErrorCatalog::for($exception);
            $request = app()->bound('request') ? request() : null;
            $requestId = self::requestId($request);

            $route = $request instanceof Request ? $request->route() : null;
            $routeName = is_object($route) ? $route->getName() : null;
            $routeAction = is_object($route) ? $route->getActionName() : null;

            // Keep operational correlation, but never persist exception messages,
            // SQL text, bindings or stack traces in the application incident log.
            // Those values can contain customer data or credentials.
            $databaseDiagnostics = self::databaseDiagnostics($exception);

            $context = [
                'event_type' => 'application_error',
                'error_code' => $definition['code'],
                'category' => $definition['category'],
                'severity' => $definition['severity'],
                'http_status' => $definition['status'],
                'user_message' => $definition['message'],
                'exception_class' => $exception::class,
                'originating_component' => $routeAction,
                'route_name' => $routeName,
                'http_method' => $request instanceof Request ? $request->method() : null,
                'http_path' => $request instanceof Request ? $request->path() : null,
                'request_id' => $requestId,
                'timestamp' => now()->toIso8601String(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                ...$databaseDiagnostics,
            ];

            $level = $definition['status'] >= 500 ? 'error' : 'warning';

            Log::log($level, 'Zazu error incident', $context);
        } catch (Throwable $loggingFailure) {
            // The fallback must not reintroduce an exception message that may
            // contain SQL, customer data or secrets.
            Log::error('Zazu error incident recorder failed', [
                'exception_class' => $loggingFailure::class,
            ]);
        }
    }

    /**
     * Add only bounded database diagnostics. Never include SQL, bindings,
     * exception messages, connection hosts, database names or credentials.
     */
    private static function databaseDiagnostics(Throwable $exception): array
    {
        if ($exception instanceof \Illuminate\Database\QueryException) {
            return self::queryExceptionDiagnostics($exception);
        }

        if ($exception instanceof \PDOException) {
            return self::pdoExceptionDiagnostics($exception);
        }

        return [];
    }

    private static function queryExceptionDiagnostics(\Illuminate\Database\QueryException $exception): array
    {
        return [
            'database_connection' => $exception->getConnectionName(),
            'database_driver' => self::databaseDriver($exception->getConnectionName()),
            'database_sqlstate' => self::sqlState($exception->errorInfo ?? null),
            'database_driver_code' => self::driverCode($exception->errorInfo ?? null),
            'database_operation' => self::databaseOperation($exception->readWriteType),
            'database_failure_class' => self::databaseFailureClass($exception, self::databaseDriver($exception->getConnectionName())),
        ];
    }

    private static function pdoExceptionDiagnostics(\PDOException $exception): array
    {
        return [
            'database_connection' => null,
            'database_driver' => null,
            'database_sqlstate' => self::sqlState($exception->errorInfo ?? null),
            'database_driver_code' => self::driverCode($exception->errorInfo ?? null),
            'database_failure_class' => self::databaseFailureClass($exception, null),
        ];
    }


    /**
     * Convert private driver text into a small, non-sensitive failure category.
     * Never persist the original driver message.
     */
    private static function databaseFailureClass(Throwable $exception, ?string $driver): string
    {
        $previous = $exception->getPrevious();
        $message = strtolower($previous?->getMessage() ?? $exception->getMessage());
        $sqlState = self::sqlState($exception instanceof \PDOException ? ($exception->errorInfo ?? null) : null);

        if ($driver === 'sqlite' || str_starts_with((string) $sqlState, 'HY')) {
            return match (true) {
                str_contains($message, 'no such table') => 'sqlite_missing_table',
                str_contains($message, 'no such column') => 'sqlite_missing_column',
                str_contains($message, 'database is locked') => 'sqlite_locked',
                str_contains($message, 'database table is locked'),
                str_contains($message, 'database schema is locked') => 'sqlite_schema_locked',
                str_contains($message, 'readonly database'),
                str_contains($message, 'read-only database') => 'sqlite_read_only',
                str_contains($message, 'disk i/o error') => 'sqlite_disk_io',
                str_contains($message, 'database disk image is malformed') => 'sqlite_malformed',
                str_contains($message, 'unable to open database file') => 'sqlite_open_failed',
                str_contains($message, 'database or disk is full') => 'sqlite_disk_full',
                str_contains($message, 'foreign key constraint failed') => 'sqlite_foreign_key',
                str_contains($message, 'unique constraint failed') => 'sqlite_unique_constraint',
                str_contains($message, 'database is busy') => 'sqlite_busy',
                default => 'sqlite_unclassified',
            };
        }

        return match ($sqlState) {
            '42P01' => 'database_missing_table',
            '42703' => 'database_missing_column',
            '23505' => 'database_unique_constraint',
            '23503' => 'database_foreign_key',
            '40001' => 'database_serialization_conflict',
            '40P01' => 'database_deadlock',
            '08006', '08003' => 'database_connection_failure',
            default => 'database_unclassified',
        };
    }

    private static function databaseDriver(string $connectionName): ?string
    {
        $driver = config("database.connections.{$connectionName}.driver");

        return is_string($driver) && preg_match('/^[a-z0-9_]{1,32}$/i', $driver) === 1
            ? $driver
            : null;
    }

    private static function sqlState(mixed $errorInfo): ?string
    {
        if (!is_array($errorInfo) || !isset($errorInfo[0]) || !is_string($errorInfo[0])) {
            return null;
        }

        return preg_match('/^[0-9A-Z]{5}$/', $errorInfo[0]) === 1 ? $errorInfo[0] : null;
    }

    private static function driverCode(mixed $errorInfo): ?int
    {
        if (!is_array($errorInfo) || !isset($errorInfo[1])) {
            return null;
        }

        $code = $errorInfo[1];
        if (is_int($code)) {
            return $code;
        }

        return is_string($code) && preg_match('/^[0-9]{1,10}$/', $code) === 1
            ? (int) $code
            : null;
    }

    private static function databaseOperation(?string $operation): ?string
    {
        return in_array($operation, ['read', 'write', 'direct'], true) ? $operation : null;
    }

    private static function requestId(?Request $request): string
    {
        if ($request instanceof Request) {
            $requestId = $request->attributes->get('zazu_request_id');

            if (is_string($requestId) && $requestId !== '') {
                return $requestId;
            }
        }

        if (app()->bound('zazu_request_id')) {
            $requestId = app('zazu_request_id');

            if (is_string($requestId) && $requestId !== '') {
                return $requestId;
            }
        }

        return (string) Str::uuid();
    }
}

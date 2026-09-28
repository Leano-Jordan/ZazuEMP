<?php

namespace App\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Authorization\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ZazuErrorCatalog
{
    public static function for(Throwable $exception): array
    {
        $code = self::codeFor($exception);
        $definition = config("zazu.errors.{$code}", config('zazu.errors.APP-001'));

        return [
            'code' => $code,
            'category' => (string) ($definition['category'] ?? 'Application'),
            'severity' => (string) ($definition['severity'] ?? 'high'),
            'headline' => (string) ($definition['headline'] ?? 'Zazu could not complete that request.'),
            'message' => (string) ($definition['message'] ?? 'We could not complete your request. Please try again.'),
            'status' => self::statusFor($exception, (int) ($definition['status'] ?? 500)),
        ];
    }

    public static function forStatus(int $status): array
    {
        $code = match ($status) {
            401 => 'AUTH-001',
            403 => 'AUTHZ-001',
            404 => 'ROUTE-001',
            405 => 'ROUTE-002',
            409 => 'BUS-001',
            419 => 'AUTH-002',
            422 => 'VAL-001',
            429 => 'SYS-002',
            502, 504 => 'API-001',
            503 => 'SYS-001',
            default => 'APP-001',
        };

        $definition = config("zazu.errors.{$code}", config('zazu.errors.APP-001'));

        return [
            'code' => $code,
            'category' => (string) ($definition['category'] ?? 'Application'),
            'severity' => (string) ($definition['severity'] ?? 'high'),
            'headline' => (string) ($definition['headline'] ?? 'Zazu could not complete that request.'),
            'message' => (string) ($definition['message'] ?? 'We could not complete your request. Please try again.'),
            'status' => $status,
        ];
    }

    /**
    * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private static function codeFor(Throwable $exception): string
    {
        if ($exception instanceof AuthenticationException) {
            return 'AUTH-001';
        }

        if ($exception instanceof AuthorizationException) {
            return 'AUTHZ-001';
        }

        if ($exception instanceof ValidationException) {
            return 'VAL-001';
        }

        if ($exception instanceof ModelNotFoundException) {
            return 'ROUTE-001';
        }

        if ($exception instanceof QueryException || $exception instanceof \PDOException) {
            return 'DB-001';
        }

        if (
            $exception instanceof FileNotFoundException
            || $exception instanceof FilesystemException
            || $exception instanceof FileException
        ) {
            return 'FILE-001';
        }

        if ($exception instanceof ConnectionException || $exception instanceof RequestException) {
            return 'API-001';
        }

        if ($exception instanceof BindingResolutionException) {
            return 'CFG-001';
        }

        if ($exception instanceof HttpExceptionInterface) {
            if ($exception->getStatusCode() === 403 && $exception->getMessage() === 'An active business workspace is required.') {
                return 'AUTHZ-002';
            }

            return match ($exception->getStatusCode()) {
                401 => 'AUTH-001',
                403 => 'AUTHZ-001',
                404 => 'ROUTE-001',
                405 => 'ROUTE-002',
                409 => 'BUS-001',
                419 => 'AUTH-002',
                422 => 'VAL-001',
                429 => 'SYS-002',
                502, 504 => 'API-001',
                503 => 'SYS-001',
                default => 'APP-001',
            };
        }

        return 'APP-001';
    }

    private static function statusFor(Throwable $exception, int $fallback): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return $fallback;
    }
}

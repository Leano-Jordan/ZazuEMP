<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class ZazuIncidentRecorder
{
    /**
    * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function record(Throwable $exception): void
    {
        try {
            $definition = ZazuErrorCatalog::for($exception);
            $request = app()->bound('request') ? request() : null;
            $requestId = self::requestId($request);

            $route = $request instanceof Request ? $request->route() : null;
            $routeName = is_object($route) ? $route->getName() : null;
            $routeAction = is_object($route) ? $route->getActionName() : null;

            $context = [
                'event_type' => 'application_error',
                'error_code' => $definition['code'],
                'category' => $definition['category'],
                'severity' => $definition['severity'],
                'http_status' => $definition['status'],
                'user_message' => $definition['message'],
                'technical_message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'originating_component' => $routeAction,
                'route_name' => $routeName,
                'http_method' => $request instanceof Request ? $request->method() : null,
                'http_path' => $request instanceof Request ? $request->path() : null,
                'request_id' => $requestId,
                'timestamp' => now()->toIso8601String(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];

            if ($exception instanceof QueryException) {
                $context['query'] = [
                    'sql' => $exception->getSql(),
                ];
            }

            $level = $definition['status'] >= 500 ? 'error' : 'warning';

            Log::log($level, 'Zazu error incident', $context);
        } catch (Throwable $loggingFailure) {
            Log::error('Zazu error incident recorder failed', [
                'exception_class' => $loggingFailure::class,
                'technical_message' => $loggingFailure->getMessage(),
            ]);
        }
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

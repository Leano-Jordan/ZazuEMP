<?php

use App\Support\ZazuErrorCatalog;
use App\Support\ZazuIncidentRecorder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\AttachRequestId::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ApplySecurityHeaders::class);
        $middleware->alias([
            'owner' => \App\Http\Middleware\EnsureBusinessOwner::class,
            'platform.admin' => \App\Http\Middleware\EnsurePlatformAdmin::class,
            'business.context' => \App\Http\Middleware\EnsureActiveBusinessContext::class,
            'permission' => \App\Http\Middleware\EnsureBusinessPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->reportable(function (Throwable $exception): void {
            ZazuIncidentRecorder::record($exception);
        });

        $exceptions->render(function (ThrottleRequestsException $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || ! $request->is('login')) {
                return null;
            }

            $retryAfter = max(1, (int) ($exception->getHeaders()['Retry-After'] ?? 60));

            return redirect()
                ->route('landing', ['auth' => 'login'])
                ->with('auth_modal', 'login')
                ->withErrors(['identifier' => "Too many sign-in attempts. Please wait {$retryAfter} seconds, then try again."]);
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            $definition = ZazuErrorCatalog::for($exception);

            if (in_array($definition['status'], [401, 422], true)) {
                return null;
            }

            $requestId = $request->attributes->get('zazu_request_id') ?: (string) Str::uuid();

            return response()->view('errors.layout', [
                'requestId' => $requestId,
                'code' => $definition['code'],
                'headline' => $definition['headline'],
                'messageText' => $definition['message'],
            ], $definition['status'])
                ->header('X-Zazu-Request-Id', $requestId)
                ->header('X-Zazu-Error-Code', $definition['code']);
        });

        $exceptions->respond(function (Response $response) {
            $request = request();
            $requestId = $request->attributes->get('zazu_request_id') ?: (string) Str::uuid();

            $response->headers->set('X-Zazu-Request-Id', $requestId);

            if (
                $response->getStatusCode() < 500
                || $request->expectsJson()
                || $request->is('api/*')
                || $response->headers->has('X-Zazu-Error-Code')
            ) {
                return $response;
            }

            $response->headers->set('X-Zazu-Error-Code', 'APP-001');

            $definition = ZazuErrorCatalog::forStatus($response->getStatusCode());

            return response()->view('errors.layout', [
                'requestId' => $requestId,
                'code' => $definition['code'],
                'headline' => $definition['headline'],
                'messageText' => $definition['message'],
            ], $response->getStatusCode())
                ->header('X-Zazu-Request-Id', $requestId)
                ->header('X-Zazu-Error-Code', $definition['code']);
        });
    })->create();

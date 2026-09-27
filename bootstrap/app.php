<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\AttachRequestId::class);
        $middleware->alias([
            'owner' => \App\Http\Middleware\EnsureBusinessOwner::class,
            'business.context' => \App\Http\Middleware\EnsureActiveBusinessContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            $requestId = $request->attributes->get('zazu_request_id') ?: (string) Str::uuid();

            return response()->view('errors.404', [
                'requestId' => $requestId,
            ], 404)->header('X-Zazu-Request-Id', $requestId);
        });

        $exceptions->respond(function (Response $response) {
            $request = request();
            $requestId = $request->attributes->get('zazu_request_id') ?: (string) Str::uuid();

            $response->headers->set('X-Zazu-Request-Id', $requestId);

            if (
                $response->getStatusCode() < 500
                || $request->expectsJson()
                || $request->is('api/*')
            ) {
                return $response;
            }

            return response()->view('errors.500', [
                'requestId' => $requestId,
                'code' => $response->getStatusCode(),
            ], $response->getStatusCode())
                ->header('X-Zazu-Request-Id', $requestId);
        });
    })->create();

<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\ApiAcceptMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . './../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', [
            ApiAcceptMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            $isDebug = config('app.debug');

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => __('validation.failed'),
                        'errors' => $e->errors(),
                    ]
                ], 422);
            }

            if ($e instanceof ApiException) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => $e->errorCode(),
                    ]
                ], $e->status());
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => __('auth.failed'),
                    ]
                ], 401);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => __('messages.not_found'),
                    ]
                ], 404);
            }

            Log::error($e);

            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $isDebug
                        ? $e->getMessage()
                        : __('messages.server_error'),
                ]
            ], 500);
        });
    })->create();

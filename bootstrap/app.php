<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 📦 Not Found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.',
                    'error' => $e->getMessage()
                ], 404);
            }
        });

        // 🚫 Fallback untuk semua API exception
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $msg = 'Unexpected Error';
                $err = 500;

                if (str_contains($e->getMessage(), '[login]')) {
                    $msg = 'Authentication failed or token expired.';
                    $err = 401;
                } elseif (str_contains($e->getMessage(), 'method is not')) {
                    $msg = 'Method not Allowed.';
                    $err = 405;
                }

                return response()->json([
                    'message' => $msg,
                    'error' => $e->getMessage()
                ], $err);
            }
        });
    })->create();

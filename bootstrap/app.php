<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // DB connection failures (error code 2002 = can't connect to MySQL)
        $exceptions->render(function (QueryException $e, Request $request) {
            $isConnectionError = in_array($e->getCode(), [2002, 2003, 2006, 2013, 1045]);
            if (!$isConnectionError) return null; // let other query exceptions bubble normally

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Service temporarily unavailable. Please try again shortly.',
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            }

            return response()->view('errors.db-down', [], Response::HTTP_SERVICE_UNAVAILABLE);
        });
    })->create();

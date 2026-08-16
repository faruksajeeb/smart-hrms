<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have access to this area.',
                ], SymfonyResponse::HTTP_FORBIDDEN);
            }

            return Inertia::render('Error', [
                'status' => SymfonyResponse::HTTP_FORBIDDEN,
                'message' => 'You do not have access to this area.',
            ])->toResponse($request)->setStatusCode(SymfonyResponse::HTTP_FORBIDDEN);
        });

        $exceptions->respond(function (SymfonyResponse $response, Throwable $exception, Request $request) {
            if ($request->expectsJson()) {
                return $response;
            }

            if (in_array($response->getStatusCode(), [
                SymfonyResponse::HTTP_FORBIDDEN,
                SymfonyResponse::HTTP_NOT_FOUND,
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR,
                SymfonyResponse::HTTP_SERVICE_UNAVAILABLE,
            ], true)) {
                return Inertia::render('Error', [
                    'status' => $response->getStatusCode(),
                    'message' => match ($response->getStatusCode()) {
                        SymfonyResponse::HTTP_FORBIDDEN => 'You do not have access to this area.',
                        SymfonyResponse::HTTP_NOT_FOUND => 'The page you are looking for could not be found.',
                        default => 'Something went wrong while loading this page.',
                    },
                ])->toResponse($request)->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();

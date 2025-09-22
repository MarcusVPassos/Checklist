<?php


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Spatie Permission 
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

// Gzip
use ErlandMuchasaj\LaravelGzip\Middleware\GzipEncodeResponse;

// Handle authorization exceptions
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
        $middleware->append(GzipEncodeResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $toFriendly = function ($request) {
            // JSON / API
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não tem permissão para essa ação.'
                ], Response::HTTP_FORBIDDEN);
            }

            // Evita loop: se não houver "previous" ou a previous for a mesma URL, manda pra home
            $previous = url()->previous();
            $same = $previous === $request->fullUrl();
            $target = (!$previous || $same) ? route('home', absolute: false) : $previous;

            return redirect($target)->with('error', 'Você não tem permissão para essa ação.');
        };

        // Policies/Gates, @can, Gate::authorize()
        $exceptions->render(function (AuthorizationException $e, $request) use ($toFriendly) {
            return $toFriendly($request);
        });

        // Spatie role/permission middlewares
        $exceptions->render(function (UnauthorizedException $e, $request) use ($toFriendly) {
            return $toFriendly($request);
        });
    })->create();

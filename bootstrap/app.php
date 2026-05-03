<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => \App\Http\Middleware\JwtMiddleware::class,

        ]);


    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->wantsJson() || $request->is('api/*') || true) {
                $status = 500;
                $message = config('app.debug') ? $e->getMessage() : 'Error interno del servidor';
                $errors = null;

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                    $message = 'Error de validación';
                    $errors = $e->errors();
                } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $status = 401;
                    $message = 'No autenticado';
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $status = 404;
                    $message = 'Ruta o recurso no encontrado';
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $status = 404;
                    $message = 'Recurso no encontrado';
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $status = $e->getStatusCode();
                    $message = $e->getMessage();
                } elseif ($e instanceof \Exception) {
                    // Business logic exceptions (like the ones thrown in AuthService)
                    $status = 400;
                    $message = $e->getMessage();
                }

                $response = [
                    'success' => false,
                    'message' => $message,
                ];

                if ($errors) {
                    $response['errors'] = $errors;
                }

                return response()->json($response, $status);
            }
        });
    })->create();

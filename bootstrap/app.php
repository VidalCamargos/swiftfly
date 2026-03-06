<?php

use App\Http\Middleware\ForceAcceptsJson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Exceptions\InvalidSortQuery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', [
            ForceAcceptsJson::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $exception): bool {
            return $request->is('v1/*') || $request->expectsJson();
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            [$status, $message, $code] = match (true) {
                $exception instanceof ValidationException => [
                    $exception->status,
                    $exception->validator->errors(),
                    'validation_error',
                ],
                $exception instanceof AuthenticationException,
                $exception instanceof TokenMismatchException,
                $exception instanceof UnauthorizedHttpException => [
                    Response::HTTP_UNAUTHORIZED,
                    Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                    null,
                ],
                $exception instanceof AuthorizationException,
                $exception instanceof AccessDeniedHttpException => [
                    Response::HTTP_FORBIDDEN,
                    Response::$statusTexts[Response::HTTP_FORBIDDEN],
                    null,
                ],
                $exception instanceof ModelNotFoundException,
                $exception instanceof NotFoundHttpException => [
                    Response::HTTP_NOT_FOUND,
                    Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    null,
                ],
                $exception instanceof InvalidFilterQuery,
                $exception instanceof InvalidSortQuery => [
                    Response::HTTP_BAD_REQUEST,
                    $exception->getMessage(),
                    null,
                ],
                $exception instanceof MethodNotAllowedHttpException => [
                    Response::HTTP_METHOD_NOT_ALLOWED,
                    Response::$statusTexts[Response::HTTP_METHOD_NOT_ALLOWED],
                    null,
                ],
                default => [null, null, null],
            };

            if (is_null($status) && is_null($message) && is_null($code)) {
                return;
            }

            return response()->json([
                ...(is_null($code) ? [] : ['code' => $code]),
                is_null($code) ? 'error' : 'errors' => $message,
            ], $status);
        });
    })
    ->withProviders([
        LaravelServiceProvider::class,
    ])
    ->create();

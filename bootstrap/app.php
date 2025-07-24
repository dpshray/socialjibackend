<?php

use App\Exceptions\ForbiddenItemAccessException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\NotOwnerException;
use App\Services\v1\OAuth\Trustap;
use App\Services\v1\Payment\TrustAppException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $responder = new class {
            use \App\Traits\ResponseTrait;
        };
        $exceptions->render(function (Throwable $e)  use ($responder) {
            if (env('APP_ENV', 'production') == 'production') {
                if ($e instanceof QueryException) {
                    return response()->json(['error' => 'Some Connection issue with Database. Please reach out to us if it is not resolved soon.'], 500);
                }
            }
            if ($e instanceof NotFoundHttpException) {
                return $responder->apiError($e->getMessage(), 404);
        }
        });
        $exceptions->render(function (ValidationException $e, Request $request) use($responder) {
            if ($request->expectsJson()) {
                $errors = collect($e->errors())->mapWithKeys(fn($i,$k) => [$k => $i[0]])->all();
                return $responder->apiError($e->getMessage(), 422, $errors);
            }
        });
        $exceptions->render(function (ForbiddenItemAccessException $e, Request $request) use($responder) {
            if ($request->expectsJson()) {
                return $responder->apiError($e->getMessage() ?? 'Forbidden', 403);
            }
        });
        $exceptions->render(function (TrustAppException $e, Request $request) use($responder) {
            if ($request->expectsJson()) {
                return $responder->apiError($e->getMessage() ?? 'Payment Error', 500);
            }
        });

    })->create();

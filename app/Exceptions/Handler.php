<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     */
    public function report(Throwable $e): void
    {
        try {
            $request = app()->bound('request') ? request() : null;
            $userId = app()->bound('auth') ? auth()->id() : null;

            Log::error($e->getMessage(), [
                'exception'      => get_class($e),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'correlation_id' => (string) ($request?->header('X-Request-Id') ?: uniqid('req_', true)),
                'user_id'        => $userId,
                'url'            => $request?->fullUrl(),
                'method'         => $request?->method(),
            ]);
        } catch (Throwable $loggingError) {
            error_log('Exception reporter failed: '.$loggingError->getMessage());
            error_log('Original: '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof AuthorizationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403);
            }
        }

        if (app()->environment('production', 'demo')) {
            if ($e instanceof TokenMismatchException) {
                return redirect($request->fullUrl())->withErrors(trans('app.msg.invalid_token'));
            }

            if ($e instanceof HttpResponseException) {
                return redirect(route('user.directory.list'))->withErrors(trans('app.msg.invalid_url'));
            }

            if (! app()->isDownForMaintenance() && $request->user()) {
                return redirect(route('whoops'))->withErrors(trans('app.msg.general_exception'));
            }
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}

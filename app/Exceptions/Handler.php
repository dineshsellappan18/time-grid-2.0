<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
        //
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
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error($e->getMessage(), [
                'exception'      => get_class($e),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'correlation_id' => (string) request()?->header('X-Request-Id', uniqid('req_', true)),
                'user_id'        => \Auth::id(),
                'url'            => request()?->fullUrl(),
                'method'         => request()?->method(),
            ]);

            return false;
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403);
            }

            return null;
        });

        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if (app()->environment('production', 'demo')) {
                return redirect($request->fullUrl())->withErrors(trans('app.msg.invalid_token'));
            }

            return null;
        });

        $this->renderable(function (Throwable $e, $request) {
            if (!app()->environment('production', 'demo')) {
                return null;
            }

            if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return redirect(route('user.directory.list'))->withErrors(trans('app.msg.invalid_url'));
            }

            if (!app()->isDownForMaintenance() && $request->user()) {
                return redirect(route('whoops'))->withErrors(trans('app.msg.general_exception'));
            }

            return null;
        });
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

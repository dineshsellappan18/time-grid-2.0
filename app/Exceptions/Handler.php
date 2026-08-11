<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception via the application logger only.
     * Third-party reporters (Rollbar) are intentionally not invoked (WO-009).
     *
     * @param  \Exception  $exception
     *
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            Log::error($exception->getMessage(), [
                'exception'      => get_class($exception),
                'file'           => $exception->getFile(),
                'line'           => $exception->getLine(),
                'correlation_id' => (string) request()->header('X-Request-Id', uniqid('req_', true)),
                'user_id'        => \Auth::id(),
                'url'            => request()->fullUrl(),
                'method'         => request()->method(),
            ]);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (app()->environment('production') || app()->environment('demo')) {
            if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect($request->fullUrl())->withErrors(trans('app.msg.invalid_token'));
            }

            if ($exception instanceof \Illuminate\Http\Exception\HttpResponseException) {
                return redirect(route('user.directory.list'))->withErrors(trans('app.msg.invalid_url'));
            }

            if (!app()->isDownForMaintenance() && $exception instanceof Exception && $request->user()) {
                return redirect(route('whoops'))->withErrors(trans('app.msg.general_exception'));
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}

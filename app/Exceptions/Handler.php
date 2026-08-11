<?php

namespace App\Exceptions;

use App\Logging\CorrelationContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Timegridio\Concierge\Exceptions\DuplicatedAppointmentException;

class Handler extends ExceptionHandler
{
    /**
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e): void
    {
        try {
            $request = app()->bound('request') ? request() : null;
            $userId = app()->bound('auth') ? auth()->id() : null;

            Log::error($e->getMessage(), [
                'exception'      => get_class($e),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'correlation_id' => CorrelationContext::id(),
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

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return $this->renderJsonEnvelope($request, $e);
        }

        if ($e instanceof AuthorizationException) {
            return redirect()->back()->withErrors(trans('app.msg.unauthorized'));
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

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return $this->envelope(401, 'unauthenticated', 'Authentication required.');
        }

        return redirect()->guest('login');
    }

    private function renderJsonEnvelope($request, Throwable $e)
    {
        if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return $this->envelope(429, 'rate_limited', 'Too many requests. Please try again later.')
                ->withHeaders(['Retry-After' => $e->getHeaders()['Retry-After'] ?? 60]);
        }

        if ($e instanceof ValidationException) {
            return $this->envelope(400, 'validation_failed', 'The given data was invalid.', [
                'fields' => $e->errors(),
            ]);
        }

        if ($e instanceof AuthorizationException) {
            return $this->envelope(403, 'forbidden', 'Access denied.');
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->envelope(404, 'not_found', 'Resource not found.');
        }

        if ($e instanceof DuplicatedAppointmentException) {
            return $this->envelope(409, 'conflict', 'Duplicate or capacity conflict.');
        }

        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $code = $this->statusToCode($status);
            $message = $this->statusToMessage($status);
            return $this->envelope($status, $code, $message);
        }

        return $this->envelope(500, 'internal_error', 'An unexpected error occurred.');
    }

    private function envelope(int $status, string $code, string $message, array $extra = [])
    {
        $body = [
            'error' => array_merge([
                'code'           => $code,
                'message'        => $message,
                'correlation_id' => CorrelationContext::id(),
            ], $extra),
        ];

        return response()->json($body, $status);
    }

    private function statusToCode(int $status): string
    {
        return match ($status) {
            400 => 'bad_request',
            401 => 'unauthenticated',
            403 => 'forbidden',
            404 => 'not_found',
            409 => 'conflict',
            422 => 'validation_failed',
            429 => 'too_many_requests',
            default => 'error',
        };
    }

    private function statusToMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad request.',
            401 => 'Authentication required.',
            403 => 'Access denied.',
            404 => 'Resource not found.',
            409 => 'Conflict.',
            422 => 'Validation failed.',
            429 => 'Too many requests.',
            default => 'An error occurred.',
        };
    }
}

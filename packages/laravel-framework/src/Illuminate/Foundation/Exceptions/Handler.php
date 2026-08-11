<?php

namespace Illuminate\Foundation\Exceptions;

use Closure;
use Psr\Log\LoggerInterface;
use Throwable;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class Handler implements ExceptionHandlerContract
{
    protected $container;

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected array $reportCallbacks = [];

    protected array $renderCallbacks = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->register();
    }

    public function register(): void
    {
        //
    }

    public function reportable(callable $reportUsing): self
    {
        $this->reportCallbacks[] = $reportUsing;

        return $this;
    }

    public function renderable(callable $renderUsing): self
    {
        $this->renderCallbacks[] = $renderUsing;

        return $this;
    }

    public function report(Throwable $e): void
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        foreach ($this->reportCallbacks as $callback) {
            $result = $callback($e);
            if ($result === false) {
                return;
            }
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Throwable $ex) {
            throw $e;
        }

        $logger->error($e->getMessage(), ['exception' => $e]);
    }

    public function shouldReport(Throwable $e): bool
    {
        return !$this->shouldntReport($e);
    }

    protected function shouldntReport(Throwable $e): bool
    {
        $dontReport = array_merge($this->dontReport, $this->internalDontReport ?? []);

        foreach ($dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    protected function prepareException(Throwable $e): Throwable
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException(403, $e->getMessage(), $e);
        }

        return $e;
    }

    public function render($request, Throwable $e)
    {
        foreach ($this->renderCallbacks as $callback) {
            if (! $this->callbackAcceptsException($callback, $e)) {
                continue;
            }

            $response = $callback($e, $request);
            if ($response !== null) {
                return $response;
            }
        }

        $e = $this->prepareException($e);

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        return $this->prepareResponse($request, $e);
    }

    /**
     * Determine if a renderable callback can accept the given exception.
     */
    protected function callbackAcceptsException(callable $callback, Throwable $e): bool
    {
        try {
            $reflection = new \ReflectionFunction(\Closure::fromCallable($callback));
        } catch (\ReflectionException $reflectionException) {
            return true;
        }

        $parameters = $reflection->getParameters();
        if ($parameters === []) {
            return true;
        }

        $type = $parameters[0]->getType();
        if (! $type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return true;
        }

        $expected = $type->getName();

        return $e instanceof $expected;
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }

    protected function prepareResponse($request, Throwable $e)
    {
        if ($this->isHttpException($e)) {
            return $this->toIlluminateResponse($this->renderHttpException($e), $e);
        } else {
            return $this->toIlluminateResponse($this->convertExceptionToResponse($e), $e);
        }
    }

    protected function toIlluminateResponse($response, Throwable $e)
    {
        if ($response instanceof SymfonyRedirectResponse) {
            $response = new RedirectResponse($response->getTargetUrl(), $response->getStatusCode(), $response->headers->all());
        } else {
            $response = new Response($response->getContent(), $response->getStatusCode(), $response->headers->all());
        }

        return $response->withException($e);
    }

    public function renderForConsole($output, Throwable $e): void
    {
        if (method_exists(ConsoleApplication::class, 'renderThrowable')) {
            (new ConsoleApplication)->renderThrowable($e, $output);

            return;
        }

        // Symfony Console < 4.4 compatibility for the path-forked framework.
        $output->writeln('<error>'.get_class($e).': '.$e->getMessage().'</error>');
        $output->writeln($e->getFile().':'.$e->getLine());
        $output->writeln($e->getTraceAsString());
    }

    protected function renderHttpException(HttpException $e)
    {
        $status = $e->getStatusCode();

        if (view()->exists("errors.{$status}")) {
            return response()->view("errors.{$status}", ['exception' => $e], $status, $e->getHeaders());
        } else {
            return $this->convertExceptionToResponse($e);
        }
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        $errors = $e->validator->errors()->getMessages();

        if ($request->expectsJson()) {
            return new JsonResponse($errors, $e->status);
        }

        return redirect()->back()->withInput(
            $request->except($this->dontFlash)
        )->withErrors($errors);
    }

    protected function convertExceptionToResponse(Throwable $e)
    {
        $statusCode = $this->isHttpException($e) ? $e->getStatusCode() : 500;
        $headers = $this->isHttpException($e) ? $e->getHeaders() : [];

        if (config('app.debug')) {
            return new SymfonyResponse(
                $this->renderExceptionContent($e),
                $statusCode,
                $headers
            );
        }

        return new SymfonyResponse('Server Error', $statusCode, $headers);
    }

    protected function renderExceptionContent(Throwable $e): string
    {
        return sprintf(
            '<h1>%s</h1><p>%s</p><pre>%s</pre>',
            get_class($e),
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }

    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
}

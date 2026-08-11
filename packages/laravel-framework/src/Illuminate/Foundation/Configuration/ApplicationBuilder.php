<?php

namespace Illuminate\Foundation\Configuration;

use Closure;
use Illuminate\Foundation\Application;

class ApplicationBuilder
{
    protected Application $app;

    protected array $globalMiddleware = [];

    protected array $middlewareGroups = [];

    protected array $routeMiddleware = [];

    protected array $middlewareAliases = [];

    protected ?Closure $exceptionUsing = null;

    protected ?Closure $scheduleUsing = null;

    protected ?string $webRoutes = null;

    protected ?string $apiRoutes = null;

    protected ?string $commandRoutes = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function withRouting(
        ?string $web = null,
        ?string $api = null,
        ?string $commands = null,
        ?string $health = null,
    ): static {
        $this->webRoutes = $web;
        $this->apiRoutes = $api;
        $this->commandRoutes = $commands;

        return $this;
    }

    public function withMiddleware(?Closure $callback = null): static
    {
        if ($callback !== null) {
            $middleware = new Middleware();
            $callback($middleware);

            $this->globalMiddleware = $middleware->getGlobal();
            $this->middlewareGroups = $middleware->getGroups();
            $this->routeMiddleware = $middleware->getAliases();
        }

        return $this;
    }

    public function withExceptions(?Closure $callback = null): static
    {
        $this->exceptionUsing = $callback;

        return $this;
    }

    public function withSchedule(Closure $callback): static
    {
        $this->scheduleUsing = $callback;

        return $this;
    }

    public function withCommands(array $commands = []): static
    {
        $this->app->instance('config.commands', $commands);

        return $this;
    }

    public function create(): Application
    {
        $this->app->instance('config.middleware.global', $this->globalMiddleware);
        $this->app->instance('config.middleware.groups', $this->middlewareGroups);
        $this->app->instance('config.middleware.aliases', $this->routeMiddleware);
        $this->app->instance('config.routes.web', $this->webRoutes);
        $this->app->instance('config.routes.api', $this->apiRoutes);
        $this->app->instance('config.routes.commands', $this->commandRoutes);

        if ($this->exceptionUsing) {
            $this->app->instance('config.exceptions.using', $this->exceptionUsing);
        }

        if ($this->scheduleUsing) {
            $this->app->instance('config.schedule.using', $this->scheduleUsing);
        }

        $this->app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \App\Http\Kernel::class
        );

        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \App\Console\Kernel::class
        );

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );

        return $this->app;
    }
}

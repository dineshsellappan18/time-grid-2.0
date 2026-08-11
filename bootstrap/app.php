<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \App\Http\Middleware\CorrelationId::class,
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Foundation\Http\Middleware\VerifyPostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\Language::class,
        ]);

        $middleware->web([
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\Language::class,
        ]);

        $middleware->api([
            'throttle:60,1',
            'bindings',
        ]);

        $middleware->alias([
            'auth'       => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'bindings'   => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'can'        => \Illuminate\Auth\Middleware\Authorize::class,
            'guest'      => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'throttle'   => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->withCommands([
        \App\Console\Commands\AutopublishBusinessVacancies::class,
        \App\Console\Commands\SendRootReport::class,
        \App\Console\Commands\SendBusinessReport::class,
        \App\Console\Commands\SyncICal::class,
    ])
    ->create();

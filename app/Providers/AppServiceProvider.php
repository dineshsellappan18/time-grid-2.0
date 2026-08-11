<?php

namespace App\Providers;

use App\Bootstrap\ApplicationKeyGuard;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        (new ApplicationKeyGuard())->assertSecureKey(
            $this->app->environment(),
            config('app.key')
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Illuminate\Contracts\Auth\Registrar::class,
            \App\TG\Registrar::class
        );

        $this->app->bind(
            \App\TG\Contracts\UserRegistrarInterface::class,
            \App\TG\UserRegistrar::class
        );

        $this->app->bind(
            \App\TG\Contracts\BusinessProvisionerInterface::class,
            \App\TG\BusinessProvisioner::class
        );

        // Local-only generators / localization-helpers removed for PHP 8.3+ install (WO-015).

        // ROLLBAR_TOKEN in .env is ignored — Rollbar provider intentionally not registered (WO-009).
    }
}

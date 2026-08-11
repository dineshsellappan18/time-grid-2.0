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

        if ($this->app->environment() == 'local') {
            $this->app->register(\Laracasts\Generators\GeneratorsServiceProvider::class);
            $this->app->register(\Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider::class);
        }

        // ROLLBAR_TOKEN in .env is ignored — Rollbar provider intentionally not registered (WO-009).
    }
}

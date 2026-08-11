<?php

namespace App\Providers;

use App\Bootstrap\ApplicationKeyGuard;
use Illuminate\Database\Eloquent\Model;
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

        // Path-fork Eloquent supports preventLazyLoading; enable only in testing until
        // remaining screens are eager-loaded. Local browsing still has many intentional
        // lazy relations (category, services, preferences, etc.).
        if ($this->app->environment('testing')) {
            Model::preventLazyLoading();
        }

        \Illuminate\Support\Facades\Blade::directive('vite', function ($expression) {
            return '<?php echo \\App\\Support\\Vite::tags('.$expression.'); ?>';
        });
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

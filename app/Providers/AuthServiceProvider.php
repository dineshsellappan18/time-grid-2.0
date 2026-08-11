<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \Timegridio\Concierge\Models\Business::class     => \App\Policies\BusinessPolicy::class,
        \Timegridio\Concierge\Models\Contact::class      => \App\Policies\ContactPolicy::class,
        \Timegridio\Concierge\Models\Appointment::class  => \App\Policies\AppointmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('ical.download', \App\Policies\IcalFeedPolicy::class.'@download');

        Gate::after(function ($user, $ability, $result) {
            if ($result === null) {
                Log::channel('security')->warning('gate.unmapped_ability', [
                    'actor'   => $user?->id,
                    'ability' => $ability,
                ]);
                return false;
            }
        });
    }
}

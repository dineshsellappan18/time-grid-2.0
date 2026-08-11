<?php

namespace App\Providers;

use App\Events\AppointmentWasCanceled;
use App\Events\AppointmentWasConfirmed;
use App\Events\NewAppointmentWasBooked;
use App\Events\NewContactWasRegistered;
use App\Events\NewSoftAppointmentWasBooked;
use App\Events\NewUserWasRegistered;
use App\Listeners\AuditAuthEvents;
use App\Listeners\AutoConfigureUserPreferences;
use App\Listeners\InvalidateAvailabilityCache;
use App\Listeners\LinkContactToExistingUser;
use App\Listeners\SendAppointmentCancellationNotification;
use App\Listeners\SendAppointmentConfirmationNotification;
use App\Listeners\SendBookingNotification;
use App\Listeners\SendMailUserWelcome;
use App\Listeners\SendSoftAppointmentValidationRequest;
use App\Listeners\UserEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewUserWasRegistered::class => [
            AutoConfigureUserPreferences::class,
            SendMailUserWelcome::class,
        ],
        NewAppointmentWasBooked::class => [
            SendBookingNotification::class,
            InvalidateAvailabilityCache::class,
        ],
        NewContactWasRegistered::class => [
            LinkContactToExistingUser::class,
        ],
        AppointmentWasConfirmed::class => [
            SendAppointmentConfirmationNotification::class,
        ],
        AppointmentWasCanceled::class => [
            SendAppointmentCancellationNotification::class,
            InvalidateAvailabilityCache::class,
        ],
        NewSoftAppointmentWasBooked::class => [
            SendSoftAppointmentValidationRequest::class,
            InvalidateAvailabilityCache::class,
        ],
    ];

    protected $subscribe = [
        UserEventListener::class,
        AuditAuthEvents::class,
    ];

    public function boot(): void
    {
        parent::boot();
    }
}

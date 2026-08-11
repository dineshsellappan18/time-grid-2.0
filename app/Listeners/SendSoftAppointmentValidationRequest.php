<?php

namespace App\Listeners;

use App\Events\NewSoftAppointmentWasBooked;
use App\TG\TransMail;

class SendSoftAppointmentValidationRequest
{
    public function __construct(
        private readonly TransMail $transmail,
    ) {
    }

    public function handle(NewSoftAppointmentWasBooked $event): void
    {
        logger()->info(__METHOD__);

        $timezone = $event->appointment->business->timezone;
        $businessName = $event->appointment->business->name;
        $businessSlug = $event->appointment->business->slug;
        $locale = $event->appointment->business->locale;
        $email = $event->appointment->contact->email;
        $code = $event->appointment->code;

        if ($event->appointment->business->pref('disable_outbound_mailing')) {
            return;
        }

        $params = [
            'appointment'  => $event->appointment,
            'link'         => $this->generateLink($businessSlug, $code, $email),
            'businessName' => $businessName,
        ];
        $header = [
            'name'  => $event->appointment->contact->firstname,
            'email' => $email,
        ];

        $this->transmail
                    ->locale($locale)
                    ->timezone($timezone)
                    ->template('guest.appointment-validation.validation')
                    ->subject('guest.appointment-validation.subject', compact('businessName'))
                    ->send($header, $params);
    }

    protected function generateLink(string $business, string $code, string $email): string
    {
        return link_to_route('user.booking.validate', null, compact('business', 'code', 'email'));
    }
}

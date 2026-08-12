<?php

namespace App\Listeners;

use App\Events\NewSoftAppointmentWasBooked;
use App\TG\TransMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSoftAppointmentValidationRequest implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(
        private readonly TransMail $transmail,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(NewSoftAppointmentWasBooked $event): void
    {
        try {
            $appointment = $event->appointment;
            $appointment->loadMissing(['business', 'contact']);
        } catch (ModelNotFoundException $e) {
            Log::warning('SendSoftAppointmentValidationRequest: model deleted before processing', [
                'exception' => $e->getMessage(),
            ]);
            $this->delete();
            return;
        }

        $timezone = $appointment->business->timezone;
        $businessName = $appointment->business->name;
        $businessSlug = $appointment->business->slug;
        $locale = $appointment->business->locale;
        $email = $appointment->contact->email;
        $code = $appointment->code;

        if ($appointment->business->pref('disable_outbound_mailing')) {
            Log::info('SendSoftAppointmentValidationRequest: skipped, outbound mailing disabled', [
                'business_id' => $appointment->business->id,
            ]);
            return;
        }

        $params = [
            'appointment'  => $appointment,
            'link'         => $this->generateLink($businessSlug, $code, $email),
            'businessName' => $businessName,
        ];
        $header = [
            'name'  => $appointment->contact->firstname,
            'email' => $email,
        ];

        $this->transmail
                    ->locale($locale)
                    ->timezone($timezone)
                    ->template('guest.appointment-validation.validation')
                    ->subject('guest.appointment-validation.subject', compact('businessName'))
                    ->send($header, $params);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendSoftAppointmentValidationRequest: job failed permanently', [
            'exception' => $exception->getMessage(),
        ]);
    }

    protected function generateLink(string $business, string $code, string $email): string
    {
        return link_to_route('user.booking.validate', null, compact('business', 'code', 'email'));
    }
}

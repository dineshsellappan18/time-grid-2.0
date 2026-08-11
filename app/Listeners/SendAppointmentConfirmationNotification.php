<?php

namespace App\Listeners;

use App\Events\AppointmentWasConfirmed;
use App\TG\TransMail;
use Fenos\Notifynder\Facades\Notifynder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAppointmentConfirmationNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public string $queue = 'notifications';

    private $transmail;

    public function __construct(TransMail $transmail)
    {
        $this->transmail = $transmail;
    }

    public function handle(AppointmentWasConfirmed $event): void
    {
        try {
            $appointment = $event->appointment;
            $appointment->loadMissing(['business', 'contact']);
        } catch (ModelNotFoundException $e) {
            Log::warning('SendAppointmentConfirmationNotification: model deleted before processing', [
                'exception' => $e->getMessage(),
            ]);
            $this->delete();
            return;
        }

        $code = $appointment->code;
        $date = $appointment->start_at->toDateString();
        $businessName = $appointment->business->name;

        Notifynder::category('appointment.confirm')
                   ->from('App\Models\User', $event->user->id)
                   ->to('Timegridio\Concierge\Models\Business', $appointment->business->id)
                   ->url('http://localhost')
                   ->extra(compact('businessName', 'code', 'date'))
                   ->send();

        if ($appointment->business->pref('disable_outbound_mailing')) {
            Log::info('SendAppointmentConfirmationNotification: skipped, outbound mailing disabled', [
                'business_id' => $appointment->business->id,
            ]);
            return;
        }

        $params = [
            'user'         => $event->user,
            'appointment'  => $appointment,
            'userName'     => $appointment->contact->firstname,
            'businessName' => $businessName,
        ];
        $header = [
            'name'  => $appointment->contact->firstname,
            'email' => $appointment->contact->email,
        ];
        $this->transmail->locale($appointment->business->locale)
                        ->timezone($event->user->pref('timezone'))
                        ->template('user.appointment-confirmation.notification')
                        ->subject('user.appointment-confirmation.subject', compact('businessName'))
                        ->send($header, $params);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendAppointmentConfirmationNotification: job failed permanently', [
            'exception' => $exception->getMessage(),
        ]);
    }
}

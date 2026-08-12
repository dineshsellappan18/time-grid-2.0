<?php

namespace App\Listeners;

use App\Events\NewAppointmentWasBooked;
use App\Models\User;
use App\TG\TransMail;
use Fenos\Notifynder\Facades\Notifynder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Contact;

class SendBookingNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    private $transmail;

    public function __construct(TransMail $transmail)
    {
        $this->transmail = $transmail;
        $this->onQueue('notifications');
    }

    public function handle(NewAppointmentWasBooked $event): void
    {
        try {
            $appointment = $event->appointment;
            $appointment->loadMissing(['business', 'contact.user']);
        } catch (ModelNotFoundException $e) {
            Log::warning('SendBookingNotification: model deleted before processing', [
                'exception' => $e->getMessage(),
            ]);
            $this->delete();
            return;
        }

        $code = $appointment->code;
        $date = $appointment->start_at->toDateString();
        $businessName = $appointment->business->name;

        Notifynder::category('appointment.reserve')
                   ->from('App\Models\User', $event->user->id)
                   ->to('Timegridio\Concierge\Models\Business', $appointment->business->id)
                   ->url('http://localhost')
                   ->extra(compact('businessName', 'code', 'date'))
                   ->send();

        if ($appointment->business->pref('disable_outbound_mailing')) {
            Log::info('SendBookingNotification: skipped, outbound mailing disabled', [
                'business_id' => $appointment->business->id,
            ]);
            return;
        }

        $this->sendEmailToContactUser($event);
        $this->sendEmailToOwner($event);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendBookingNotification: job failed permanently', [
            'exception' => $exception->getMessage(),
        ]);
    }

    protected function sendEmailToContactUser($event): void
    {
        if (!$user = $event->appointment->contact->user) {
            return;
        }

        $destinationEmail = $this->getDestinationEmail($user, $event->appointment->contact);

        $params = [
            'user'        => $user,
            'appointment' => $event->appointment,
            'userName'    => $event->appointment->contact->firstname,
        ];
        $header = [
            'name'  => $event->appointment->contact->firstname,
            'email' => $destinationEmail,
        ];
        $email = [
            'header'   => $header,
            'params'   => $params,
            'locale'   => $event->appointment->business->locale,
            'timezone' => $user->pref('timezone'),
            'template' => 'user.appointment-notification.notification',
            'subject'  => 'user.appointment-notification.subject',
        ];
        $this->sendemail($email);
    }

    protected function sendEmailToOwner($event): void
    {
        $params = [
            'user'        => $event->appointment->business->owner(),
            'appointment' => $event->appointment,
            'ownerName'   => $event->appointment->business->owner()->name,
        ];
        $header = [
            'name'  => $event->appointment->business->owner()->name,
            'email' => $event->appointment->business->owner()->email,
        ];
        $email = [
            'header'   => $header,
            'params'   => $params,
            'locale'   => $event->appointment->business->locale,
            'timezone' => $event->appointment->business->owner()->pref('timezone'),
            'template' => 'manager.appointment-notification.notification',
            'subject'  => 'manager.appointment-notification.subject',
        ];
        $this->sendemail($email);
    }

    protected function sendEmail($email): void
    {
        extract($email);

        $this->transmail->locale($locale)
                        ->timezone($timezone)
                        ->template($template)
                        ->subject($subject)
                        ->send($header, $params);
    }

    protected function getDestinationEmail(User $user, Contact $contact): string
    {
        return $contact->email ?: $user->email;
    }
}

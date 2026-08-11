<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Timegridio\Concierge\Models\Appointment;

class NewAppointmentWasBooked extends Event
{
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Appointment $appointment,
    ) {
    }
}

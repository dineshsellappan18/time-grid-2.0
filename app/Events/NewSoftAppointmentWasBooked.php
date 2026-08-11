<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Timegridio\Concierge\Models\Appointment;

class NewSoftAppointmentWasBooked extends Event
{
    use SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
    ) {
    }
}

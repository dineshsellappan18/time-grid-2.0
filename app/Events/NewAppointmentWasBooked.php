<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Timegridio\Concierge\Models\Appointment;

class NewAppointmentWasBooked extends Event
{
    use SerializesModels;

    public $user;

    public $appointment;

    public function __construct(User $user, Appointment $appointment)
    {
        $this->user = $user;
        $this->appointment = $appointment;
    }
}

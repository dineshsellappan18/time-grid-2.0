<?php

namespace App\Policies;

use App\Models\User;
use App\TG\AuditLogger;
use Illuminate\Auth\Access\HandlesAuthorization;
use Timegridio\Concierge\Models\Appointment;

class AppointmentPolicy
{
    use HandlesAuthorization;

    private AuditLogger $audit;

    public function __construct()
    {
        $this->audit = app(AuditLogger::class);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        $allowed = $this->isAuthorised($user, $appointment);

        if (!$allowed) {
            $this->audit->denied('appointment.view', 'appointment', $appointment->id);
        }

        return $allowed;
    }

    public function confirm(User $user, Appointment $appointment): bool
    {
        $allowed = $this->isAuthorised($user, $appointment);

        if (!$allowed) {
            $this->audit->denied('appointment.confirm', 'appointment', $appointment->id);
        }

        return $allowed;
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        $allowed = $this->isAuthorised($user, $appointment);

        if (!$allowed) {
            $this->audit->denied('appointment.cancel', 'appointment', $appointment->id);
        }

        return $allowed;
    }

    public function serve(User $user, Appointment $appointment): bool
    {
        if (!$this->isBusinessOwner($user, $appointment)) {
            $this->audit->denied('appointment.serve', 'appointment', $appointment->id);
            return false;
        }

        return true;
    }

    public function reschedule(User $user, Appointment $appointment): bool
    {
        $allowed = $this->isAuthorised($user, $appointment);

        if (!$allowed) {
            $this->audit->denied('appointment.reschedule', 'appointment', $appointment->id);
        }

        return $allowed;
    }

    public function annotate(User $user, Appointment $appointment): bool
    {
        $allowed = $this->isAuthorised($user, $appointment);

        if (!$allowed) {
            $this->audit->denied('appointment.annotate', 'appointment', $appointment->id);
        }

        return $allowed;
    }

    private function isAuthorised(User $user, Appointment $appointment): bool
    {
        return $this->isBusinessOwner($user, $appointment)
            || $this->isAppointmentIssuer($user, $appointment);
    }

    private function isBusinessOwner(User $user, Appointment $appointment): bool
    {
        $business = $appointment->business;

        if ($business === null) {
            return false;
        }

        return $user->isOwnerOf($business);
    }

    private function isAppointmentIssuer(User $user, Appointment $appointment): bool
    {
        return (int) $appointment->issuer_id === (int) $user->id;
    }
}

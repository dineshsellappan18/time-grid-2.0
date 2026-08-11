<?php

namespace App\Listeners;

use App\TG\Availability\AvailabilityService;
use Illuminate\Support\Facades\Log;

class InvalidateAvailabilityCache
{
    public function handle(object $event): void
    {
        $appointment = $this->extractAppointment($event);

        if ($appointment === null) {
            return;
        }

        $businessId = $appointment->business_id;
        $serviceId = $appointment->service_id;
        $date = $appointment->start_at?->toDateString();

        if ($businessId === null || $serviceId === null || $date === null) {
            Log::warning('availability.invalidation_skipped', [
                'reason' => 'missing_identifiers',
                'event' => get_class($event),
            ]);

            return;
        }

        AvailabilityService::invalidateForBooking($businessId, $serviceId, $date);
    }

    protected function extractAppointment(object $event): ?object
    {
        if (property_exists($event, 'appointment')) {
            return $event->appointment;
        }

        if (method_exists($event, 'getAppointment')) {
            return $event->getAppointment();
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Timegridio\Concierge\Models\Business;

class ICalController extends Controller
{
    public function download(Request $request, Business $business, $token)
    {
        Log::info('ical.download', [
            'actor'     => null,
            'resource'  => 'business',
            'operation' => 'ical_download',
            'context'   => ['business_id' => $business->id],
        ]);

        $appointments = $this->loadAppointments($business);

        if ($appointments->count() > 100) {
            return $this->streamResponse($business, $appointments);
        }

        return $this->bufferedResponse($business, $appointments);
    }

    protected function loadAppointments(Business $business)
    {
        $businessAppointments = $business->bookings()
            ->with(['contact:id,firstname,lastname', 'service:id,name', 'business:id,slug'])
            ->active()
            ->get();

        $ownerAppointments = $business->owner()->appointments()
            ->with(['contact:id,firstname,lastname', 'service:id,name', 'business:id,slug'])
            ->active()
            ->get();

        return $businessAppointments->merge($ownerAppointments);
    }

    protected function bufferedResponse(Business $business, $appointments)
    {
        $vCalendar = new Calendar($business->slug);
        $vCalendar->setPublishedTTL('PT1H');

        foreach ($appointments as $appointment) {
            $vCalendar->addComponent($this->buildEvent($business, $appointment));
        }

        $content = $vCalendar->render();

        return response($content)
                    ->header('Content-Type', 'text/calendar; charset=utf-8')
                    ->header('Content-Disposition', 'attachment; filename="calendar.ics"');
    }

    protected function streamResponse(Business $business, $appointments): StreamedResponse
    {
        return response()->stream(function () use ($business, $appointments) {
            $vCalendar = new Calendar($business->slug);
            $vCalendar->setPublishedTTL('PT1H');

            foreach ($appointments as $appointment) {
                $vCalendar->addComponent($this->buildEvent($business, $appointment));
            }

            echo $vCalendar->render();
        }, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="calendar.ics"',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    protected function buildEvent(Business $business, $appointment): Event
    {
        $vEvent = new Event();

        $startAt = new \DateTime(
            $appointment->start_at->copy()->timezone($business->timezone)->toDateTimeString(),
            new \DateTimeZone($business->timezone)
        );
        $endAt = new \DateTime(
            $appointment->finish_at->copy()->timezone($business->timezone)->toDateTimeString(),
            new \DateTimeZone($business->timezone)
        );

        $vEvent->setDtStart($startAt);
        $vEvent->setDtEnd($endAt);
        $vEvent->setStatus($this->mapStatus($appointment->status));

        $businessSlug = $appointment->business->slug ?? $business->slug;
        $vEvent->setUniqueId($businessSlug . ':' . $appointment->code . '@timegrid.io');

        $contactName = $appointment->contact->firstname ?? 'Unknown';
        $serviceName = $appointment->service->name ?? 'Unknown';

        $summary = $contactName . '/' .
                   $serviceName . '@' .
                   $businessSlug .
                   ' [' . $appointment->code . ']';

        $vEvent->setSummary($summary);
        $vEvent->setDescription($appointment->comments);
        $vEvent->setUseTimezone(true);

        return $vEvent;
    }

    protected function mapStatus($status)
    {
        $mapping = [
            'R' => 'TENTATIVE',
            'C' => 'CONFIRMED',
            'A' => 'CANCELLED',
            'S' => 'CONFIRMED',
        ];

        return Arr::get($mapping, $status, 'TENTATIVE');
    }
}

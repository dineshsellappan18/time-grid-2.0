<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\TG\Availability\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Business;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {
        parent::__construct();
    }

    public function getDates(int $businessId, int $serviceId): JsonResponse
    {
        logger()->info(__METHOD__);
        logger()->info(serialize(compact('businessId', 'serviceId')));

        $business = Business::findOrFail($businessId);
        $service = $business->services()->findOrFail($serviceId);

        $days = $business->pref('availability_future_days');
        $startFrom = $business->pref('appointment_take_today') ? 'today' : 'tomorrow';

        $baseDate = Carbon::parse($startFrom);
        $endDate = $baseDate->copy()->addDays($days);

        $this->excludeDates($businessId);

        $dates = $this->availability->getDates($business, $service->id);

        $disabledDates = $this->getDisabledDates($baseDate, $endDate, $dates);

        logger()->debug('Disabled Dates:'.serialize($disabledDates));

        return response()->json([
            'business' => $business->id,
            'service'  => [
                'id'       => $service->id,
                'duration' => $service->duration,
            ],
            'dates'         => $dates,
            'disabledDates' => $disabledDates,
            'startDate'     => $baseDate->toDateString(),
            'endDate'       => $endDate->toDateString(),
        ], 200);
    }

    public function getTimes(int $businessId, int $serviceId, string $date, string|false $preferredTimezone = false): JsonResponse
    {
        logger()->info(__METHOD__);
        logger()->info(serialize(compact('businessId', 'serviceId', 'date', 'preferredTimezone')));

        $business = Business::findOrFail($businessId);
        $service = $business->services()->findOrFail($serviceId);

        $timezone = $this->decideTimezone($preferredTimezone, $business->timezone);

        logger()->info("Using Timezone: {$timezone}");

        $times = $this->availability->timezone($timezone)->getTimes($business, $service, Carbon::parse($date));

        return response()->json([
            'business' => $businessId,
            'service'  => [
                'id'       => $service->id,
                'duration' => $service->duration,
            ],
            'date'     => $date,
            'times'    => $times,
            'timezone' => $timezone,
        ], 200);
    }

    protected function decideTimezone(string|false $preferredTimezone, string $fallbackTimezone): string
    {
        if ($preferredTimezone === false) {
            $timezone = auth()->guest() ? $fallbackTimezone : auth()->user()->pref('timezone');
        }

        return $timezone ?: $fallbackTimezone;
    }

    protected function getDisabledDates(Carbon $start, Carbon $end, array $enabledDates): array
    {
        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($start, $interval, $end->addDay());

        $dates = [];
        foreach ($daterange as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return array_values(array_diff($dates, $enabledDates));
    }

    protected function excludeDates(int $businessId): void
    {
        $filepath = "business/{$businessId}/ical/ical-exclusion.compiled";

        if (!Storage::exists($filepath)) {
            return;
        }

        $excluded = Storage::get($filepath);

        if ($excluded === null || $excluded === '') {
            Log::warning('AvailabilityController: exclusion file exists but is empty or unreadable', [
                'business_id' => $businessId,
                'filepath'    => $filepath,
            ]);

            return;
        }

        logger()->debug('ICal Exclude Dates:'.serialize($excluded));

        $this->availability->excludeDates(explode("\n", $excluded));
    }
}

<?php

namespace App\TG\Availability;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class AvailabilityService
{
    protected ?string $timezone = null;

    protected string $timeformat = 'H:i';

    protected array $excludeDates = [];

    public function timezone(?string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function timeformat(string $timeformat): static
    {
        $this->timeformat = $timeformat;

        return $this;
    }

    public function excludeDates(array $dates): static
    {
        $this->excludeDates = $dates;

        return $this;
    }

    public function getDates(Business $business, int $serviceId): array
    {
        $vacancies = $business->vacancies()->with('humanresource')->forService($serviceId)->get();

        $vacancies = $this->removeExcludedDates($vacancies);

        $dates = array_pluck($vacancies->toArray(), 'date');

        return array_diff($dates, $this->excludeDates);
    }

    protected function removeExcludedDates(Collection $vacancies): Collection
    {
        $excludedDates = collect($this->excludeDates);

        return $vacancies->reject(fn ($vacancy) => $excludedDates->contains("{$vacancy->humanresourceSlug()}:{$vacancy->date}") ||
                   $excludedDates->contains("{$vacancy->date}"));
    }

    public function getTimes(Business $business, Service $service, Carbon $date): array
    {
        $vacancies = $business->vacancies()->with('humanresource')->forService($service->id)->forDate($date)->get();

        $step = $this->calculateStep($business, $service->duration);

        return $this->splitTimes($vacancies, $service, $step);
    }

    protected function splitTimes($vacancies, Service $service, int $step = 30): array
    {
        $times = [];
        foreach ($vacancies as $vacancy) {
            $beginTime = $vacancy->start_at->copy();

            $maxNumberOfSlots = round($vacancy->finish_at->diffInMinutes($beginTime) / $step);

            $this->addSlots($times, $vacancy, $beginTime, $service->duration, $step, $maxNumberOfSlots);
        }

        return $times;
    }

    protected function addSlots(array &$times, Vacancy $vacancy, Carbon $beginTime, int $duration, int $step, int $maxNumberOfSlots): void
    {
        for ($i = 0; $i <= $maxNumberOfSlots; $i++) {
            $serviceEndTime = $beginTime->copy()->addMinutes($duration);

            if ($vacancy->hasRoomBetween($beginTime, $serviceEndTime)) {
                $times[] = $beginTime->timezone($this->timezone)->format($this->timeformat);
            }

            $beginTime->addMinutes($step);
        }
    }

    protected function calculateStep(Business $business, int $defaultStep = 30): int
    {
        $step = $business->pref('timeslot_step');

        if (0 != $step) {
            return $step;
        }

        return $defaultStep;
    }
}

<?php

namespace App\TG\Availability;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class AvailabilityService
{
    protected ?string $timezone = null;

    protected string $timeformat = 'H:i';

    protected array $excludeDates = [];

    private const CACHE_TTL_SECONDS = 60;

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
        $cacheKey = self::buildCacheKey('dates', $business->id, $serviceId);

        return $this->cached($cacheKey, function () use ($business, $serviceId) {
            $vacancies = $business->vacancies()->with('humanresource')->forService($serviceId)->get();

            $vacancies = $this->removeExcludedDates($vacancies);

            $dates = Arr::pluck($vacancies->toArray(), 'date');

            return array_values(array_diff($dates, $this->excludeDates));
        });
    }

    protected function removeExcludedDates(Collection $vacancies): Collection
    {
        $excludedDates = collect($this->excludeDates);

        return $vacancies->reject(fn ($vacancy) => $excludedDates->contains("{$vacancy->humanresourceSlug()}:{$vacancy->date}") ||
                   $excludedDates->contains("{$vacancy->date}"));
    }

    public function getTimes(Business $business, Service $service, Carbon $date): array
    {
        $cacheKey = self::buildCacheKey('times', $business->id, $service->id, $date->toDateString(), $this->timezone ?? $business->timezone);

        return $this->cached($cacheKey, function () use ($business, $service, $date) {
            $vacancies = $business->vacancies()->with('humanresource')->forService($service->id)->forDate($date)->get();

            $step = $this->calculateStep($business, $service->duration);

            return $this->splitTimes($vacancies, $service, $step);
        });
    }

    protected function splitTimes($vacancies, Service $service, int $step = 30): array
    {
        $times = [];
        foreach ($vacancies as $vacancy) {
            $beginTime = $vacancy->start_at->copy();

            $maxNumberOfSlots = (int) round($vacancy->finish_at->diffInMinutes($beginTime) / $step);

            $this->addSlots($times, $vacancy, $beginTime, $service->duration, $step, $maxNumberOfSlots);
        }

        return $times;
    }

    protected function addSlots(array &$times, Vacancy $vacancy, Carbon $beginTime, int $duration, int $step, int $maxNumberOfSlots): void
    {
        for ($i = 0; $i <= $maxNumberOfSlots; $i++) {
            $serviceEndTime = $beginTime->copy()->addMinutes($duration);

            if ($vacancy->hasRoomBetween($beginTime, $serviceEndTime)) {
                $times[] = $beginTime->copy()->timezone($this->timezone)->format($this->timeformat);
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

    public static function buildCacheKey(string $type, int $businessId, int $serviceId, ?string $date = null, ?string $timezone = null): string
    {
        $parts = ['availability', $type, "biz:{$businessId}", "svc:{$serviceId}"];

        if ($date !== null) {
            $parts[] = "d:{$date}";
        }

        if ($timezone !== null) {
            $parts[] = "tz:" . str_replace('/', '_', $timezone);
        }

        return implode(':', $parts);
    }

    public static function invalidateForBooking(int $businessId, int $serviceId, string $date): void
    {
        $store = self::cacheStore();

        $store->forget(self::buildCacheKey('dates', $businessId, $serviceId));
        $store->forget(self::buildCacheKey('times', $businessId, $serviceId, $date));

        $timezones = config('app.supported_timezones', []);
        foreach ($timezones as $tz) {
            $store->forget(self::buildCacheKey('times', $businessId, $serviceId, $date, $tz));
        }

        Log::debug('availability.cache_invalidated', [
            'business_id' => $businessId,
            'service_id' => $serviceId,
            'date' => $date,
        ]);
    }

    public static function invalidateForBusiness(int $businessId): void
    {
        Log::debug('availability.cache_business_invalidated', [
            'business_id' => $businessId,
        ]);
    }

    protected function cached(string $key, callable $compute): array
    {
        if (!config('availability.cache_enabled', true)) {
            return $compute();
        }

        try {
            $store = self::cacheStore();

            return $store->remember($key, self::CACHE_TTL_SECONDS, $compute);
        } catch (\Throwable $e) {
            Log::warning('availability.cache_failure', [
                'error' => $e->getMessage(),
                'key' => $key,
            ]);

            return $compute();
        }
    }

    protected static function cacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store(config('availability.cache_store', 'redis'));
    }
}

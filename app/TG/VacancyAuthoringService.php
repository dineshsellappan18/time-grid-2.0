<?php

namespace App\TG;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\HumanResource;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Vacancy\VacancyParser;

class VacancyAuthoringService
{
    public function __construct(
        private readonly Concierge $concierge,
        private readonly VacancyParser $vacancyParser,
    ) {
    }

    public function publishSimpleMode(Business $business, array $publishedVacancies): bool
    {
        $changed = false;

        foreach ($publishedVacancies as $date => $vacancy) {
            foreach ($vacancy as $serviceId => $capacity) {
                $startAt = Carbon::parse($date . ' ' . $business->pref('start_at') . ' ' . $business->timezone);
                $finishAt = Carbon::parse($date . ' ' . $business->pref('finish_at') . ' ' . $business->timezone);

                if ($capacity === '') {
                    continue;
                }

                $changed = true;

                $this->concierge
                     ->business($business)
                     ->vacancies()
                     ->publish($date, $startAt, $finishAt, $serviceId, $capacity);
            }
        }

        return $changed;
    }

    public function publishBatchMode(Business $business, string $statements, bool $unpublish, bool $remember): bool
    {
        $this->concierge->business($business);

        if ($unpublish) {
            $this->concierge->vacancies()->unpublish();
        }

        $parsedVacancies = $this->vacancyParser->parseStatements($statements);

        $updated = $this->concierge->vacancies()->updateBatch($business, $parsedVacancies);

        if ($updated && $remember) {
            $this->rememberStatements($business->id, $statements);
        }

        return (bool) $updated;
    }

    public function updateServiceVacancies(Business $business, int $serviceId, array $weekdays): bool
    {
        $service = $business->services()->find($serviceId);

        if (!$service) {
            return false;
        }

        $humanResource = $business->humanresources()->first();

        if (!$humanResource) {
            return false;
        }

        $startAt = $business->pref('start_at');
        $finishAt = $business->pref('finish_at');

        $statements = $this->buildStatements($service, $humanResource, $weekdays, $startAt, $finishAt, $business->timezone);
        $parsedVacancies = $this->vacancyParser->parseStatements($statements);

        $this->concierge->business($business);

        $vacanciesToWipe = $business->vacancies()->where(['service_id' => $service->id]);
        if ($vacanciesToWipe->exists()) {
            $vacanciesToWipe->delete();
        }

        return (bool) $this->concierge->vacancies()->updateBatch($business, $parsedVacancies);
    }

    public function generateAvailability(Business $business, string $from, int $days): array
    {
        return $this->concierge
                    ->business($business)
                    ->vacancies()
                    ->generateAvailability($from, $days);
    }

    public function buildTimetable(Business $business, int $daysQuantity): array
    {
        $vacancies = $business->vacancies()->with('Appointments')->get();

        return $this->concierge
                    ->business($business)
                    ->timetable()
                    ->buildTimetable($vacancies, 'today', $daysQuantity);
    }

    public function getTemplate(Business $business): string
    {
        return $this->concierge
                    ->business($business)
                    ->vacancies()
                    ->builder()
                    ->getTemplate($business, $business->services()->first()) ?? '';
    }

    public function recallStatements(int $businessId): ?string
    {
        $file = $this->getStatementsFile($businessId);

        if (!Storage::exists($file)) {
            return null;
        }

        return Storage::get($file);
    }

    public function rememberStatements(int $businessId, string $statements): bool
    {
        return Storage::put(
            $this->getStatementsFile($businessId),
            $statements
        );
    }

    protected function buildStatements(Service $service, HumanResource $humanResource, array $weekdays, string $startAt, string $finishAt, string $timezone): string
    {
        $out = [];

        $out[] = "{$service->slug}:{$humanResource->slug}";
        $dates = [];
        foreach (array_keys($weekdays) as $day) {
            for ($i = 0; $i < 4; $i++) {
                $dates[] = Carbon::parse($day . " +$i weeks " . $timezone)->toDateString();
            }
        }
        $out[] = ' ' . implode(',', $dates);
        $out[] = "  {$startAt} - {$finishAt}";

        return implode("\n", $out);
    }

    protected function getStatementsFile(int $businessId): string
    {
        return 'business' . DIRECTORY_SEPARATOR . $businessId . DIRECTORY_SEPARATOR . 'vacancy-statements.txt';
    }
}

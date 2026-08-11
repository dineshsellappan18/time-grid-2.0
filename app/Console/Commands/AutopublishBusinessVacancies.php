<?php

namespace App\Console\Commands;

use App\TG\TransMail;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Vacancy\VacancyParser;

class AutopublishBusinessVacancies extends Command
{
    protected $signature = 'business:vacancies {business?}';

    protected $description = 'Autopublish Business Vacancies';

    public function __construct(
        protected Concierge $concierge,
        protected TransMail $transmail,
        protected VacancyParser $vacancyParser,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $businessId = $this->argument('business');

        if ($businessId === null) {
            $this->info('Scanning all businesses...');
            $this->scanBusinesses();
        } else {
            $this->info("Publishing for specified businessId:{$businessId}");
            $business = Business::findOrFail($businessId);
            $this->publishVacancies($business);
        }

        return 0;
    }

    protected function getArguments(): array
    {
        return [
            ['business', InputArgument::OPTIONAL, 'Business to publish vacancies.'],
        ];
    }

    protected function scanBusinesses(): void
    {
        $businesses = Business::all();
        foreach ($businesses as $business) {
            $this->publishVacancies($business);
        }
    }

    protected function publishVacancies(Business $business): bool
    {
        $this->info(__METHOD__);
        $this->info("Publishing vacancies for businessId:{$business->id}");

        $publishedVacancies = $this->vacancyParser->parseStatements($this->recallStatements($business->id));

        if (!$this->autopublishVacancies($business)) {
            $this->info('Skipped autopublishing vacancies');

            return false;
        }

        if (!$this->concierge->business($business)->vacancies()->updateBatch($business, $publishedVacancies)) {
            return false;
        }

        return true;
    }

    protected function autopublishVacancies(Business $business): bool
    {
        return (bool) $business->pref('vacancy_autopublish');
    }

    protected function recallStatements(int $businessId): ?string
    {
        if (!Storage::exists($this->getStatementsFile($businessId))) {
            return null;
        }

        return Storage::get(
            $this->getStatementsFile($businessId)
        );
    }

    protected function getStatementsFile(int $businessId): string
    {
        return 'business'.DIRECTORY_SEPARATOR.$businessId.DIRECTORY_SEPARATOR.'vacancy-statements.txt';
    }
}

<?php

namespace App\Console\Commands;

use App\TG\TransMail;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Mail;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Business;

class SendBusinessReport extends Command
{
    protected $signature = 'business:report {business?}';

    protected $description = 'Send Business report';

    public function __construct(
        protected Concierge $concierge,
        protected TransMail $transmail,
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
            $this->info("Sending to specified businessId:{$businessId}");
            $business = Business::findOrFail($businessId);
            $this->sendBusinessReport($business);
        }

        return 0;
    }

    protected function getArguments(): array
    {
        return [
            ['business', InputArgument::OPTIONAL, 'Business to generate the report.'],
        ];
    }

    protected function scanBusinesses(): void
    {
        $businesses = Business::all();
        foreach ($businesses as $business) {
            $this->sendBusinessReport($business);
        }
    }

    protected function sendBusinessReport(Business $business): bool
    {
        $this->info(__METHOD__);
        $this->info("Sending to businessId:{$business->id}");

        $appointments = $this->concierge->business($business)->getActiveAppointments();

        if ($this->skipReport($business, count($appointments))) {
            $this->info('Skipped report');

            return false;
        }

        $owner = $business->owners()->first();

        $ownerName = $owner->name;
        $businessName = $business->name;
        $date = date('Y-m-d');
        $header = [
            'email' => $owner->email,
            'name'  => $ownerName,
        ];
        $this->transmail->locale($business->locale)
                        ->timezone($business->timezone)
                        ->template('manager.business-report.schedule')
                        ->subject('manager.business-report.subject', compact('businessName', 'date'))
                        ->send($header, compact('business', 'appointments', 'ownerName'));

        return true;
    }

    protected function skipReport(Business $business, int $appointmentsCount): bool
    {
        return !($this->enabledReports($business) && $this->hasAppointments($appointmentsCount));
    }

    protected function enabledReports(Business $business): bool
    {
        return (bool) $business->pref('report_daily_schedule');
    }

    protected function hasAppointments(int $appointmentsCount): bool
    {
        return 0 !== $appointmentsCount;
    }
}

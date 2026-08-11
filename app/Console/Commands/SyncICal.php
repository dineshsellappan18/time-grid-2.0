<?php

namespace App\Console\Commands;

use App\Jobs\FetchICalFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Timegridio\Concierge\Models\Business;

class SyncICal extends Command
{
    protected $signature = 'ical:sync {business?}';

    protected $description = 'Dispatch iCal fetch jobs for businesses with calendar links';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $businessId = $this->argument('business');

        if ($businessId === null) {
            $this->info('Dispatching iCal sync jobs for all businesses');
            $this->scanBusinesses();

            return 0;
        }

        $this->info("Dispatching iCal sync jobs for business {$businessId}");

        $business = Business::findOrFail($businessId);
        $this->processBusiness($business);

        return 0;
    }

    protected function scanBusinesses(): void
    {
        Business::chunk(50, function ($businesses) {
            foreach ($businesses as $business) {
                $this->processBusiness($business);
            }
        });
    }

    protected function processBusiness(Business $business): void
    {
        $humanresources = $business->humanresources()->whereNotNull('calendar_link')->get();

        if ($humanresources->isNotEmpty()) {
            Storage::delete("business/{$business->id}/ical/ical-exclusion.compiled");
        }

        foreach ($humanresources as $humanresource) {
            FetchICalFile::dispatch($humanresource)->onQueue('ical-sync');

            Log::info('ical:sync dispatched FetchICalFile', [
                'business_id'      => $business->id,
                'humanresource_id' => $humanresource->id,
            ]);
        }

        $this->info("  Dispatched {$humanresources->count()} job(s) for business {$business->id}");
    }
}

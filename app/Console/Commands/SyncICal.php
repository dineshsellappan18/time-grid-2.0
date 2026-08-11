<?php

namespace App\Console\Commands;

use App\TG\Availability\ICalSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Timegridio\Concierge\Models\Business;

class SyncICal extends Command
{
    protected $signature = 'ical:sync {business?}';

    protected $description = 'Sync ICal';

    public function __construct(
        protected ICalSyncService $icalsync,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $businessId = $this->argument('business');

        if ($businessId === null) {
            $this->info('Syncing ICal for all businesses');
            $this->scanBusinesses();

            return 0;
        }

        $this->info("Syncing ICal for {$businessId}");

        $business = Business::findOrFail($businessId);

        $this->processBusiness($business);

        return 0;
    }

    protected function scanBusinesses(): void
    {
        $businesses = Business::all();
        foreach ($businesses as $business) {
            $this->processBusiness($business);
        }
    }

    protected function processBusiness(Business $business): void
    {
        $humanresources = $business->humanresources()->whereNotNull('calendar_link')->get();

        if ($humanresources) {
            Storage::delete("business/{$business->id}/ical/ical-exclusion.compiled");
        }

        $this->processHumanresources($humanresources);
    }

    protected function processHumanresources($humanresources): void
    {
        foreach ($humanresources as $humanresource) {
            $this->icalsync->humanresource($humanresource)->sync();
        }
    }
}

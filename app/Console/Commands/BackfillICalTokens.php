<?php

namespace App\Console\Commands;

use App\TG\Business\Token as BusinessToken;
use App\TG\ICalTokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;

class BackfillICalTokens extends Command
{
    protected $signature = 'ical:backfill-tokens {--chunk=100}';

    protected $description = 'Backfill ical_tokens rows for existing businesses from their legacy derived tokens (idempotent)';

    public function __construct(
        private readonly ICalTokenService $tokenService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $created = 0;
        $skipped = 0;

        $this->info('Starting iCal token backfill...');

        Business::query()
            ->whereNull('deleted_at')
            ->chunkById($chunkSize, function ($businesses) use (&$created, &$skipped) {
                foreach ($businesses as $business) {
                    $legacyToken = (new BusinessToken($business))->generate();

                    $inserted = $this->tokenService->backfillLegacy($business, $legacyToken);

                    if ($inserted) {
                        $created++;
                    } else {
                        $skipped++;
                    }
                }
            });

        $this->info("Backfill complete: {$created} created, {$skipped} skipped (already exist).");

        Log::info('ical.backfill_complete', [
            'actor'     => null,
            'resource'  => 'ical_tokens',
            'operation' => 'backfill',
            'context'   => ['created' => $created, 'skipped' => $skipped],
        ]);

        return self::SUCCESS;
    }
}

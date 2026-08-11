<?php

namespace App\Console\Commands;

use App\TG\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class PurgeContactData extends Command
{
    protected $signature = 'contacts:purge-retention
        {--chunk=1000 : Number of rows per batch}
        {--limit=0 : Max rows to process (0 = all eligible)}
        {--dry-run : Preview without deleting}';

    protected $description = 'Erase restricted PII from contacts with no appointment in the last 24 months. Respects hold flags and active references.';

    public function handle(AuditLogger $audit): int
    {
        $retentionMonths = config('retention.contacts.months');
        $minimumMonths = config('retention.contacts.minimum_months');

        if ($retentionMonths < $minimumMonths) {
            $this->error("Retention window ({$retentionMonths} months) is below policy minimum ({$minimumMonths} months). Aborting.");
            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subMonths($retentionMonths);
        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Purging contact PII for contacts with no appointment since {$cutoff->toDateString()}...");

        $eligibleQuery = $this->buildEligibleQuery($cutoff);
        $beforeCount = (clone $eligibleQuery)->count();

        if ($beforeCount === 0) {
            $this->info('No contacts eligible for purge.');
            return self::SUCCESS;
        }

        $this->info("Found {$beforeCount} contacts eligible for erasure.");

        if ($dryRun) {
            $this->info('[DRY RUN] No data erased.');
            return self::SUCCESS;
        }

        $processed = 0;
        $erased = 0;
        $skipped = 0;

        $eligibleQuery->chunkById($chunkSize, function ($contacts) use (&$processed, &$erased, &$skipped, $limit, $cutoff) {
            foreach ($contacts as $contact) {
                if ($limit > 0 && $processed >= $limit) {
                    return false;
                }

                if ($this->hasHold($contact)) {
                    $skipped++;
                    $processed++;
                    continue;
                }

                if ($this->hasActiveAppointment($contact->id)) {
                    $skipped++;
                    $processed++;
                    continue;
                }

                $this->eraseContactPii($contact->id);
                $erased++;
                $processed++;
            }
        });

        $this->info("Purge complete: {$erased} erased, {$skipped} skipped (held/referenced).");

        $audit->append(
            action: 'contacts.purge_retention',
            resourceType: 'contacts',
            outcome: 'success',
            changes: [
                'before_count' => $beforeCount,
                'erased_count' => $erased,
                'skipped_count' => $skipped,
                'retention_months' => $retentionMonths,
                'cutoff_date' => $cutoff->toDateString(),
            ],
            actorType: 'system',
        );

        return self::SUCCESS;
    }

    private function buildEligibleQuery($cutoff)
    {
        return DB::table('contacts')
            ->whereNull('retention_hold')
            ->where(function ($q) use ($cutoff) {
                $q->whereNotExists(function ($sub) use ($cutoff) {
                    $sub->select(DB::raw(1))
                        ->from('appointments')
                        ->whereColumn('appointments.contact_id', 'contacts.id')
                        ->where('appointments.created_at', '>=', $cutoff);
                })
                ->where(function ($q2) use ($cutoff) {
                    $q2->where('contacts.created_at', '<', $cutoff)
                        ->orWhereNotNull('contacts.pii_backfilled_at');
                });
            })
            ->whereNotNull('contacts.pii_backfilled_at');
    }

    private function hasHold(object $contact): bool
    {
        return !empty($contact->retention_hold);
    }

    private function hasActiveAppointment(int $contactId): bool
    {
        return DB::table('appointments')
            ->where('contact_id', $contactId)
            ->where(function ($q) {
                $q->where('start_at', '>=', now())
                    ->orWhereNull('deleted_at');
            })
            ->where('start_at', '>=', now())
            ->exists();
    }

    private function eraseContactPii(int $contactId): void
    {
        DB::table('contacts')
            ->where('id', $contactId)
            ->update([
                'nin' => null,
                'nin_hash' => null,
                'mobile' => null,
                'mobile_hash' => null,
                'birthdate' => null,
                'email' => null,
                'postal_address' => null,
                'firstname' => '[erased]',
                'lastname' => '[erased]',
                'deleted_at' => now(),
            ]);
    }
}

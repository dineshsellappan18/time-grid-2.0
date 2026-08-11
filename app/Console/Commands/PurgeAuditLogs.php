<?php

namespace App\Console\Commands;

use App\TG\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeAuditLogs extends Command
{
    protected $signature = 'audit:purge
        {--chunk=1000 : Number of rows per batch}
        {--limit=0 : Max rows to delete (0 = all eligible)}
        {--dry-run : Preview without deleting}';

    protected $description = 'Physically delete audit_logs rows older than the configured retention window (default 400 days). Batched, idempotent, resumable.';

    public function handle(AuditLogger $audit): int
    {
        $retentionDays = config('retention.audit_logs.days');
        $minimumDays = config('retention.audit_logs.minimum_days');

        if ($retentionDays < $minimumDays) {
            $this->error("Retention window ({$retentionDays} days) is below policy minimum ({$minimumDays} days). Aborting.");
            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subDays($retentionDays);
        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Purging audit_logs older than {$cutoff->toDateString()} ({$retentionDays} days)...");

        $beforeCount = DB::table('audit_logs')->where('occurred_at', '<', $cutoff)->count();

        if ($beforeCount === 0) {
            $this->info('No rows eligible for purge.');
            return self::SUCCESS;
        }

        $this->info("Found {$beforeCount} rows eligible for deletion.");

        if ($dryRun) {
            $this->info('[DRY RUN] No rows deleted.');
            return self::SUCCESS;
        }

        $deleted = 0;

        while (true) {
            if ($limit > 0 && $deleted >= $limit) {
                break;
            }

            $batchLimit = ($limit > 0) ? min($chunkSize, $limit - $deleted) : $chunkSize;

            $ids = DB::table('audit_logs')
                ->where('occurred_at', '<', $cutoff)
                ->orderBy('id')
                ->limit($batchLimit)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            DB::table('audit_logs')->whereIn('id', $ids->all())->delete();
            $deleted += $ids->count();

            $this->line("  Deleted batch of {$ids->count()} (total: {$deleted})");
        }

        $this->info("Purge complete: {$deleted} rows deleted.");

        $audit->append(
            action: 'audit.purge',
            resourceType: 'audit_logs',
            outcome: 'success',
            changes: [
                'before_count' => $beforeCount,
                'deleted_count' => $deleted,
                'retention_days' => $retentionDays,
                'cutoff_date' => $cutoff->toDateString(),
            ],
            actorType: 'system',
        );

        return self::SUCCESS;
    }
}

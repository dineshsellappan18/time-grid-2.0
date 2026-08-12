<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ICalGuardDivergenceReport extends Command
{
    protected $signature = 'ical:divergence-report {--days=14}';

    protected $description = 'Generate divergence and feed availability report for iCal guard shadow period';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("iCal Guard Divergence Report — Last {$days} days");
        $this->newLine();

        $this->reportDivergences($days);
        $this->newLine();
        $this->reportFeedAvailability($days);
        $this->newLine();
        $this->reportDenials($days);
        $this->newLine();
        $this->assessCutoverReadiness($days);

        return self::SUCCESS;
    }

    private function reportDivergences(int $days): void
    {
        $this->info('=== Divergence Counts (daily) ===');

        $results = DB::table('audit_logs')
            ->selectRaw('DATE(occurred_at) as day, COUNT(*) as count')
            ->where('action', 'ical.access')
            ->where('outcome', 'denied')
            ->whereRaw(
                config('database.default') === 'pgsql'
                    ? "changes->>'reason' = 'divergence'"
                    : "JSON_EXTRACT(changes, '$.reason') = 'divergence'"
            )
            ->where('occurred_at', '>=', now()->subDays($days))
            ->groupByRaw('DATE(occurred_at)')
            ->orderBy('day')
            ->get();

        if ($results->isEmpty()) {
            $this->line('  No divergences recorded in the last ' . $days . ' days.');
            $this->line('  STATUS: PASS (zero divergences)');
            return;
        }

        $this->table(['Date', 'Divergences'], $results->map(fn ($r) => [$r->day, $r->count])->toArray());

        $lastSevenDays = $results->filter(fn ($r) => $r->day >= now()->subDays(7)->toDateString());
        $totalLastSeven = $lastSevenDays->sum('count');

        if ($totalLastSeven === 0) {
            $this->line('  STATUS: PASS (zero divergences in final 7 days)');
        } else {
            $this->error("  STATUS: FAIL ({$totalLastSeven} divergences in final 7 days — cutover blocked)");
        }
    }

    private function reportFeedAvailability(int $days): void
    {
        $this->info('=== Feed Availability ===');

        $total = DB::table('audit_logs')
            ->where('action', 'ical.access')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->count();

        $successful = DB::table('audit_logs')
            ->where('action', 'ical.access')
            ->where('outcome', 'success')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->count();

        if ($total === 0) {
            $this->line('  No feed access records in the last ' . $days . ' days.');
            $this->line('  STATUS: CANNOT_VERIFY (no traffic)');
            return;
        }

        $availability = round(($successful / $total) * 100, 3);

        $this->line("  Total requests: {$total}");
        $this->line("  Successful:     {$successful}");
        $this->line("  Availability:   {$availability}%");

        if ($availability >= 99.0) {
            $this->line('  STATUS: PASS (>= 99%)');
        } else {
            $this->error("  STATUS: FAIL ({$availability}% < 99% — cutover blocked)");
        }
    }

    private function reportDenials(int $days): void
    {
        $this->info('=== Denial Breakdown ===');

        $results = DB::table('audit_logs')
            ->selectRaw(
                config('database.default') === 'pgsql'
                    ? "changes->>'reason' as reason, COUNT(*) as count"
                    : "JSON_EXTRACT(changes, '$.reason') as reason, COUNT(*) as count"
            )
            ->where('action', 'ical.access')
            ->where('outcome', 'denied')
            ->where('occurred_at', '>=', now()->subDays($days))
            ->groupByRaw(
                config('database.default') === 'pgsql'
                    ? "changes->>'reason'"
                    : "JSON_EXTRACT(changes, '$.reason')"
            )
            ->get();

        if ($results->isEmpty()) {
            $this->line('  No denials recorded.');
            return;
        }

        $this->table(['Reason', 'Count'], $results->map(fn ($r) => [$r->reason ?? 'unknown', $r->count])->toArray());
    }

    private function assessCutoverReadiness(int $days): void
    {
        $this->info('=== Cutover Readiness Assessment ===');

        $mode = config('ical.guard_mode', 'shadow');
        $this->line("  Current mode: {$mode}");

        if ($mode === 'enforced') {
            $this->line('  Guard is already in enforced mode.');
            return;
        }

        $this->line('  Criteria:');
        $this->line('    1. Zero divergences over final 7 consecutive days');
        $this->line('    2. Feed availability >= 99% across shadow window');
        $this->line('    3. Evidence pack published and signed off');
        $this->line('    4. Rollback rehearsed and MTTR < 2 minutes');
        $this->newLine();
        $this->line('  To enforce: set ICAL_GUARD_MODE=enforced in environment configuration');
        $this->line('  To rollback: set ICAL_GUARD_MODE=shadow (no redeploy required)');
    }
}

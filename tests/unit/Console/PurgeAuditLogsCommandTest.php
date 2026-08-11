<?php

namespace Tests\Unit\Console;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurgeAuditLogsCommandTest extends TestCase
{
    use DatabaseTransactions;

    private function seedAuditRow(int $daysAgo): int
    {
        return DB::table('audit_logs')->insertGetId([
            'actor_type'     => 'system',
            'actor_id'       => null,
            'action'         => 'test.action',
            'resource_type'  => 'test',
            'resource_id'    => 1,
            'outcome'        => 'success',
            'correlation_id' => 'corr-test-' . $daysAgo,
            'ip_hash'        => hash('sha256', 'test'),
            'occurred_at'    => Carbon::now()->subDays($daysAgo),
        ]);
    }

    public function test_rows_past_boundary_are_deleted(): void
    {
        $oldId = $this->seedAuditRow(401);
        $recentId = $this->seedAuditRow(100);

        $this->artisan('audit:purge')->assertExitCode(0);

        $this->assertNull(DB::table('audit_logs')->find($oldId));
        $this->assertNotNull(DB::table('audit_logs')->find($recentId));
    }

    public function test_row_exactly_at_boundary_is_retained(): void
    {
        $boundaryId = $this->seedAuditRow(400);

        $this->artisan('audit:purge')->assertExitCode(0);

        $this->assertNotNull(DB::table('audit_logs')->find($boundaryId));
    }

    public function test_dry_run_deletes_nothing(): void
    {
        $oldId = $this->seedAuditRow(500);

        $this->artisan('audit:purge', ['--dry-run' => true])->assertExitCode(0);

        $this->assertNotNull(DB::table('audit_logs')->find($oldId));
    }

    public function test_limit_caps_deletion(): void
    {
        $this->seedAuditRow(450);
        $this->seedAuditRow(460);
        $this->seedAuditRow(470);

        $this->artisan('audit:purge', ['--limit' => 1])->assertExitCode(0);

        $remaining = DB::table('audit_logs')
            ->where('occurred_at', '<', Carbon::now()->subDays(400))
            ->count();
        $this->assertEquals(2, $remaining);
    }

    public function test_second_run_is_noop(): void
    {
        $this->seedAuditRow(450);

        $this->artisan('audit:purge')->assertExitCode(0);
        $this->artisan('audit:purge')->assertExitCode(0);
    }

    public function test_refuses_below_minimum_window(): void
    {
        config(['retention.audit_logs.days' => 100]);

        $this->artisan('audit:purge')->assertExitCode(1);
    }

    public function test_writes_audit_row_after_purge(): void
    {
        $this->seedAuditRow(500);

        $countBefore = DB::table('audit_logs')->where('action', 'audit.purge')->count();
        $this->artisan('audit:purge')->assertExitCode(0);
        $countAfter = DB::table('audit_logs')->where('action', 'audit.purge')->count();

        $this->assertEquals($countBefore + 1, $countAfter);
    }
}

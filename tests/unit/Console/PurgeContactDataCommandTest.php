<?php

namespace Tests\Unit\Console;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class PurgeContactDataCommandTest extends TestCase
{
    use DatabaseTransactions;

    private function seedContact(array $overrides = []): int
    {
        return DB::table('contacts')->insertGetId(array_merge([
            'firstname'       => 'Test',
            'lastname'        => 'Contact',
            'gender'          => 'M',
            'nin'             => Crypt::encryptString('12345'),
            'nin_hash'        => hash('sha256', '12345'),
            'mobile'          => Crypt::encryptString('+1234'),
            'mobile_hash'     => hash('sha256', '1234'),
            'birthdate'       => Crypt::encryptString('1990-01-01'),
            'email'           => 'test@test.com',
            'pii_backfilled_at' => Carbon::now()->subMonths(25),
            'retention_hold'  => null,
            'created_at'      => Carbon::now()->subMonths(30),
            'updated_at'      => Carbon::now()->subMonths(25),
        ], $overrides));
    }

    private function seedAppointment(int $contactId, Carbon $createdAt): void
    {
        DB::table('appointments')->insert([
            'business_id' => 1,
            'contact_id'  => $contactId,
            'service_id'  => 1,
            'start_at'    => $createdAt,
            'finish_at'   => $createdAt->copy()->addHour(),
            'duration'    => 60,
            'status'      => 'R',
            'hash'        => md5(uniqid()),
            'created_at'  => $createdAt,
            'updated_at'  => $createdAt,
        ]);
    }

    public function test_contact_with_old_appointment_is_erased(): void
    {
        $id = $this->seedContact();
        $this->seedAppointment($id, Carbon::now()->subMonths(30));

        $this->artisan('contacts:purge-retention')->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertEquals('[erased]', $row->firstname);
        $this->assertNull($row->nin);
        $this->assertNull($row->mobile);
    }

    public function test_contact_with_recent_appointment_is_not_erased(): void
    {
        $id = $this->seedContact();
        $this->seedAppointment($id, Carbon::now()->subMonths(6));

        $this->artisan('contacts:purge-retention')->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertEquals('Test', $row->firstname);
    }

    public function test_contact_under_hold_is_not_erased(): void
    {
        $id = $this->seedContact(['retention_hold' => 'legal-hold-2026']);
        $this->seedAppointment($id, Carbon::now()->subMonths(30));

        $this->artisan('contacts:purge-retention')->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertEquals('Test', $row->firstname);
    }

    public function test_contact_with_future_appointment_is_not_erased(): void
    {
        $id = $this->seedContact();
        $this->seedAppointment($id, Carbon::now()->subMonths(30));
        $this->seedAppointment($id, Carbon::now()->addDays(7));

        $this->artisan('contacts:purge-retention')->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertEquals('Test', $row->firstname);
    }

    public function test_contact_with_no_appointments_uses_creation_date(): void
    {
        $id = $this->seedContact(['created_at' => Carbon::now()->subMonths(30)]);

        $this->artisan('contacts:purge-retention')->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertEquals('[erased]', $row->firstname);
    }

    public function test_dry_run_does_not_erase(): void
    {
        $id = $this->seedContact();
        $this->seedAppointment($id, Carbon::now()->subMonths(30));

        $this->artisan('contacts:purge-retention', ['--dry-run' => true])->assertExitCode(0);

        $row = DB::table('contacts')->find($id);
        $this->assertEquals('Test', $row->firstname);
    }

    public function test_limit_caps_processing(): void
    {
        $id1 = $this->seedContact();
        $id2 = $this->seedContact();
        $this->seedAppointment($id1, Carbon::now()->subMonths(30));
        $this->seedAppointment($id2, Carbon::now()->subMonths(30));

        $this->artisan('contacts:purge-retention', ['--limit' => 1])->assertExitCode(0);

        $erased = DB::table('contacts')
            ->whereIn('id', [$id1, $id2])
            ->where('firstname', '[erased]')
            ->count();
        $this->assertEquals(1, $erased);
    }

    public function test_second_run_is_noop(): void
    {
        $id = $this->seedContact();
        $this->seedAppointment($id, Carbon::now()->subMonths(30));

        $this->artisan('contacts:purge-retention')->assertExitCode(0);
        $this->artisan('contacts:purge-retention')->assertExitCode(0);
    }

    public function test_refuses_below_minimum_window(): void
    {
        config(['retention.contacts.months' => 6]);

        $this->artisan('contacts:purge-retention')->assertExitCode(1);
    }

    public function test_writes_audit_row_after_purge(): void
    {
        $id = $this->seedContact();
        $this->seedAppointment($id, Carbon::now()->subMonths(30));

        $countBefore = DB::table('audit_logs')
            ->where('action', 'contacts.purge_retention')
            ->count();

        $this->artisan('contacts:purge-retention')->assertExitCode(0);

        $countAfter = DB::table('audit_logs')
            ->where('action', 'contacts.purge_retention')
            ->count();

        $this->assertEquals($countBefore + 1, $countAfter);
    }
}

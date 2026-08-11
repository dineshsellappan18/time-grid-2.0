<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeds deterministic data for retention purge tests.
 * Includes audit rows and contacts on both sides of retention boundaries,
 * a contact with a recent appointment, and a contact under hold.
 */
class RetentionPurgeFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAuditRows();
        $this->seedContacts();
    }

    private function seedAuditRows(): void
    {
        $now = Carbon::now();
        $rows = [];

        for ($i = 0; $i < 10; $i++) {
            $rows[] = [
                'actor_type'     => 'system',
                'actor_id'       => null,
                'action'         => 'fixture.old',
                'resource_type'  => 'test',
                'resource_id'    => $i,
                'outcome'        => 'success',
                'correlation_id' => 'corr-old-' . $i,
                'ip_hash'        => hash('sha256', 'fixture-' . $i),
                'occurred_at'    => $now->copy()->subDays(450 + $i),
            ];
        }

        for ($i = 0; $i < 5; $i++) {
            $rows[] = [
                'actor_type'     => 'user',
                'actor_id'       => 1,
                'action'         => 'fixture.recent',
                'resource_type'  => 'test',
                'resource_id'    => $i,
                'outcome'        => 'success',
                'correlation_id' => 'corr-recent-' . $i,
                'ip_hash'        => hash('sha256', 'fixture-recent-' . $i),
                'occurred_at'    => $now->copy()->subDays(100 + $i),
            ];
        }

        $rows[] = [
            'actor_type'     => 'user',
            'actor_id'       => 1,
            'action'         => 'fixture.boundary',
            'resource_type'  => 'test',
            'resource_id'    => 99,
            'outcome'        => 'success',
            'correlation_id' => 'corr-boundary',
            'ip_hash'        => hash('sha256', 'boundary'),
            'occurred_at'    => $now->copy()->subDays(400),
        ];

        DB::table('audit_logs')->insert($rows);
    }

    private function seedContacts(): void
    {
        $now = Carbon::now();

        $expiredId = DB::table('contacts')->insertGetId([
            'firstname'         => 'Expired',
            'lastname'          => 'Contact',
            'gender'            => 'F',
            'nin'               => Crypt::encryptString('EXP-001'),
            'nin_hash'          => hash('sha256', 'exp001'),
            'mobile'            => Crypt::encryptString('+1EXP'),
            'mobile_hash'       => hash('sha256', '1exp'),
            'birthdate'         => Crypt::encryptString('1980-01-01'),
            'email'             => 'expired@fixture.test',
            'pii_backfilled_at' => $now->copy()->subMonths(25),
            'retention_hold'    => null,
            'created_at'        => $now->copy()->subMonths(30),
            'updated_at'        => $now->copy()->subMonths(25),
        ]);

        DB::table('appointments')->insert([
            'business_id' => 1,
            'contact_id'  => $expiredId,
            'service_id'  => 1,
            'start_at'    => $now->copy()->subMonths(28),
            'finish_at'   => $now->copy()->subMonths(28)->addHour(),
            'duration'    => 60,
            'status'      => 'C',
            'hash'        => md5('expired-fixture'),
            'created_at'  => $now->copy()->subMonths(28),
            'updated_at'  => $now->copy()->subMonths(28),
        ]);

        $recentId = DB::table('contacts')->insertGetId([
            'firstname'         => 'Recent',
            'lastname'          => 'Active',
            'gender'            => 'M',
            'nin'               => Crypt::encryptString('REC-001'),
            'nin_hash'          => hash('sha256', 'rec001'),
            'mobile'            => Crypt::encryptString('+1REC'),
            'mobile_hash'       => hash('sha256', '1rec'),
            'birthdate'         => Crypt::encryptString('1990-05-15'),
            'email'             => 'recent@fixture.test',
            'pii_backfilled_at' => $now->copy()->subMonths(1),
            'retention_hold'    => null,
            'created_at'        => $now->copy()->subMonths(3),
            'updated_at'        => $now->copy()->subMonths(1),
        ]);

        DB::table('appointments')->insert([
            'business_id' => 1,
            'contact_id'  => $recentId,
            'service_id'  => 1,
            'start_at'    => $now->copy()->subMonths(1),
            'finish_at'   => $now->copy()->subMonths(1)->addHour(),
            'duration'    => 60,
            'status'      => 'C',
            'hash'        => md5('recent-fixture'),
            'created_at'  => $now->copy()->subMonths(1),
            'updated_at'  => $now->copy()->subMonths(1),
        ]);

        DB::table('contacts')->insert([
            'firstname'         => 'Held',
            'lastname'          => 'LegalHold',
            'gender'            => 'F',
            'nin'               => Crypt::encryptString('HOLD-001'),
            'nin_hash'          => hash('sha256', 'hold001'),
            'mobile'            => null,
            'mobile_hash'       => null,
            'birthdate'         => null,
            'email'             => 'held@fixture.test',
            'pii_backfilled_at' => $now->copy()->subMonths(25),
            'retention_hold'    => 'legal-hold-case-2026-001',
            'created_at'        => $now->copy()->subMonths(36),
            'updated_at'        => $now->copy()->subMonths(25),
        ]);
    }
}

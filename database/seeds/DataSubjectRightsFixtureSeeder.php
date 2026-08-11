<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeds a contact linked to two businesses with appointments in each,
 * verifying tenant scoping of export and erasure.
 */
class DataSubjectRightsFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $biz1 = DB::table('businesses')->insertGetId([
            'name' => 'DSR Business Alpha',
            'slug' => 'dsr-alpha',
            'description' => 'Test business for DSR',
            'timezone' => 'UTC',
            'strategy' => 'timeslot',
            'created_at' => $now->copy()->subYear(),
            'updated_at' => $now,
        ]);

        $biz2 = DB::table('businesses')->insertGetId([
            'name' => 'DSR Business Beta',
            'slug' => 'dsr-beta',
            'description' => 'Second test business for DSR',
            'timezone' => 'UTC',
            'strategy' => 'dateslot',
            'created_at' => $now->copy()->subYear(),
            'updated_at' => $now,
        ]);

        $contactId = DB::table('contacts')->insertGetId([
            'firstname'         => 'MultiTenant',
            'lastname'          => 'Subject',
            'gender'            => 'F',
            'nin'               => Crypt::encryptString('DSR-NIN-001'),
            'nin_hash'          => hash('sha256', 'dsr-nin-001'),
            'mobile'            => Crypt::encryptString('+44DSR123'),
            'mobile_hash'       => hash('sha256', '44dsr123'),
            'birthdate'         => Crypt::encryptString('1988-03-22'),
            'email'             => 'multitenant@fixture.test',
            'postal_address'    => '42 Rights Avenue',
            'pii_backfilled_at' => $now,
            'retention_hold'    => null,
            'created_at'        => $now->copy()->subMonths(6),
            'updated_at'        => $now,
        ]);

        DB::table('business_contact')->insert([
            ['business_id' => $biz1, 'contact_id' => $contactId, 'created_at' => $now->copy()->subMonths(6)],
            ['business_id' => $biz2, 'contact_id' => $contactId, 'created_at' => $now->copy()->subMonths(3)],
        ]);

        $svc1 = DB::table('services')->insertGetId([
            'business_id' => $biz1,
            'name' => 'DSR Service A',
            'slug' => 'dsr-svc-a',
            'duration' => 60,
            'created_at' => $now->copy()->subYear(),
            'updated_at' => $now,
        ]);

        $svc2 = DB::table('services')->insertGetId([
            'business_id' => $biz2,
            'name' => 'DSR Service B',
            'slug' => 'dsr-svc-b',
            'duration' => 30,
            'created_at' => $now->copy()->subYear(),
            'updated_at' => $now,
        ]);

        DB::table('appointments')->insert([
            [
                'business_id' => $biz1,
                'contact_id'  => $contactId,
                'service_id'  => $svc1,
                'start_at'    => $now->copy()->subMonths(2),
                'finish_at'   => $now->copy()->subMonths(2)->addHour(),
                'duration'    => 60,
                'status'      => 'C',
                'hash'        => md5('dsr-appt-alpha-1'),
                'created_at'  => $now->copy()->subMonths(2),
                'updated_at'  => $now->copy()->subMonths(2),
            ],
            [
                'business_id' => $biz1,
                'contact_id'  => $contactId,
                'service_id'  => $svc1,
                'start_at'    => $now->copy()->subWeeks(2),
                'finish_at'   => $now->copy()->subWeeks(2)->addHour(),
                'duration'    => 60,
                'status'      => 'C',
                'hash'        => md5('dsr-appt-alpha-2'),
                'created_at'  => $now->copy()->subWeeks(2),
                'updated_at'  => $now->copy()->subWeeks(2),
            ],
            [
                'business_id' => $biz2,
                'contact_id'  => $contactId,
                'service_id'  => $svc2,
                'start_at'    => $now->copy()->subMonth(),
                'finish_at'   => $now->copy()->subMonth()->addMinutes(30),
                'duration'    => 30,
                'status'      => 'C',
                'hash'        => md5('dsr-appt-beta-1'),
                'created_at'  => $now->copy()->subMonth(),
                'updated_at'  => $now->copy()->subMonth(),
            ],
        ]);
    }
}

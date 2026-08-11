<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Deterministic fixtures for PII encryption tests.
 * Provides contacts with varied nin, mobile, and birthdate formats
 * including edge cases (null, international formatting, non-ASCII).
 */
class ContactPiiFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            [
                'firstname' => 'Alice',
                'lastname' => 'Encryption',
                'gender' => 'F',
                'nin' => '12345678',
                'mobile' => '+447911123456',
                'mobile_country' => 'GB',
                'birthdate' => '1990-05-15',
                'email' => 'alice@fixture.test',
            ],
            [
                'firstname' => 'Bob',
                'lastname' => 'Nullfields',
                'gender' => 'M',
                'nin' => null,
                'mobile' => null,
                'mobile_country' => null,
                'birthdate' => null,
                'email' => 'bob@fixture.test',
            ],
            [
                'firstname' => 'Carlos',
                'lastname' => 'International',
                'gender' => 'M',
                'nin' => 'ÄÖ-2024/001',
                'mobile' => '+34612345678',
                'mobile_country' => 'ES',
                'birthdate' => '2000-12-31',
                'email' => 'carlos@fixture.test',
            ],
            [
                'firstname' => 'Dana',
                'lastname' => 'MalformedMobile',
                'gender' => 'F',
                'nin' => 'X-1',
                'mobile' => '0044 7911 123 456',
                'mobile_country' => 'GB',
                'birthdate' => '1975-01-01',
                'email' => 'dana@fixture.test',
            ],
            [
                'firstname' => 'Eve',
                'lastname' => 'EdgeCase',
                'gender' => 'F',
                'nin' => '00000000',
                'mobile' => '+1',
                'mobile_country' => 'US',
                'birthdate' => '1900-01-01',
                'email' => 'eve@fixture.test',
            ],
        ];

        $now = now();
        foreach ($contacts as $contact) {
            DB::table('contacts')->insert(array_merge($contact, [
                'nin_hash' => null,
                'mobile_hash' => null,
                'pii_backfilled_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}

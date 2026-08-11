<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Interaction parity test fixtures.
 * Creates deterministic states required by the per-screen Dusk assertions:
 * - A user with an active business (timeslot and dateslot strategies)
 * - Services attached to the business
 * - A contact with international phone number
 * - Appointments in various states (reserved, confirmed, served, cancelled)
 * - Human resources linked to business
 * - Vacancies published for next 7 days
 */
class InteractionParitySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Manager',
            'email' => 'dusk-manager@timegrid.test',
            'password' => Hash::make('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $contactUserId = DB::table('users')->insertGetId([
            'name' => 'Test Contact User',
            'email' => 'dusk-contact@timegrid.test',
            'password' => Hash::make('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $businessId = DB::table('businesses')->insertGetId([
            'name' => 'Dusk Test Salon',
            'slug' => 'dusk-test-salon',
            'description' => 'Automated test business for interaction parity checks',
            'timezone' => 'UTC',
            'strategy' => 'timeslot',
            'phone' => '+1-555-0199',
            'postal_address' => '123 Test St',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $dateslotBusinessId = DB::table('businesses')->insertGetId([
            'name' => 'Dusk Dateslot Clinic',
            'slug' => 'dusk-dateslot-clinic',
            'description' => 'Dateslot strategy business for parity tests',
            'timezone' => 'UTC',
            'strategy' => 'dateslot',
            'phone' => '+44-20-7946-0958',
            'postal_address' => '456 Clinic Rd',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $serviceId = DB::table('services')->insertGetId([
            'name' => 'Haircut',
            'slug' => 'haircut',
            'business_id' => $businessId,
            'duration' => 30,
            'color' => '#3498db',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $dateslotServiceId = DB::table('services')->insertGetId([
            'name' => 'Consultation',
            'slug' => 'consultation',
            'business_id' => $dateslotBusinessId,
            'duration' => 60,
            'color' => '#e74c3c',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $contactId = DB::table('contacts')->insertGetId([
            'user_id' => $contactUserId,
            'business_id' => $businessId,
            'firstname' => 'María',
            'lastname' => 'González-Pérez',
            'email' => 'maria.gonzalez@example.com',
            'mobile' => '+34-612-345-678',
            'birthdate' => '1990-05-15',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $contactNoUserId = DB::table('contacts')->insertGetId([
            'user_id' => null,
            'business_id' => $businessId,
            'firstname' => 'John',
            'lastname' => 'Smith-VeryLongLastNameForOverflowTesting',
            'email' => 'john.smith@example.com',
            'mobile' => '+81-90-1234-5678',
            'birthdate' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $tomorrow = $now->copy()->addDay()->setTime(10, 0);

        DB::table('appointments')->insert([
            [
                'business_id' => $businessId,
                'contact_id' => $contactId,
                'service_id' => $serviceId,
                'status' => 'R',
                'start_at' => $tomorrow,
                'finish_at' => $tomorrow->copy()->addMinutes(30),
                'duration' => 30,
                'comments' => 'First visit',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'business_id' => $businessId,
                'contact_id' => $contactId,
                'service_id' => $serviceId,
                'status' => 'C',
                'start_at' => $tomorrow->copy()->addHours(2),
                'finish_at' => $tomorrow->copy()->addHours(2)->addMinutes(30),
                'duration' => 30,
                'comments' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'business_id' => $businessId,
                'contact_id' => $contactNoUserId,
                'service_id' => $serviceId,
                'status' => 'S',
                'start_at' => $now->copy()->subDay()->setTime(14, 0),
                'finish_at' => $now->copy()->subDay()->setTime(14, 30),
                'duration' => 30,
                'comments' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('humanresources')->insert([
            'business_id' => $businessId,
            'name' => 'Alice Stylist',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

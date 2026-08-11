<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

/**
 * WO-004 — characterization anchors for booking / availability fixtures.
 * Captures that the deterministic TestingDatabaseSeeder shape remains loadable.
 */
class BookingFixtureCharacterizationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function deterministic_fixture_graph_can_be_assembled()
    {
        $owner = factory(App\Models\User::class)->create([
            'username' => 'char_manager',
            'email'    => 'char-manager@example.test',
        ]);

        $business = factory(Business::class)->create([
            'name' => 'Characterization Venue',
            'plan' => 'free',
        ]);
        $business->owners()->save($owner);

        $contact = factory(Contact::class)->create([
            'email' => 'char-guest@example.test',
        ]);
        $business->contacts()->save($contact);

        $service = factory(Service::class)->make([
            'name'     => 'Characterization Service',
            'duration' => 30,
        ]);
        $service->business()->associate($business);
        $service->save();

        $vacancy = factory(Vacancy::class)->make([
            'date'      => '2030-01-15',
            'start_at'  => '2030-01-15 09:00:00',
            'finish_at' => '2030-01-15 17:00:00',
            'capacity'  => 1,
        ]);
        $vacancy->business()->associate($business);
        $vacancy->service()->associate($service);
        $vacancy->save();

        $appointment = factory(Appointment::class)->make([
            'status'   => Appointment::STATUS_CONFIRMED,
            'start_at' => \Carbon\Carbon::parse('2030-01-15 10:00:00'),
            'duration' => 30,
        ]);
        $appointment->business()->associate($business);
        $appointment->contact()->associate($contact);
        $appointment->service()->associate($service);
        $appointment->vacancy()->associate($vacancy);
        $appointment->issuer()->associate($owner);
        $appointment->save();

        $this->assertEquals(1, $business->fresh()->services()->count());
        $this->assertEquals(1, $business->fresh()->vacancies()->count());
        $this->assertEquals(1, $business->fresh()->bookings()->count());
        $this->assertSame('Characterization Venue', $business->fresh()->name);
    }
}

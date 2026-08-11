<?php

use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use App\Models\User;
use Timegridio\Concierge\Models\Vacancy;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class TestingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds for a Demo Fixture.
     *
     * @return void
     */
    public function run()
    {
        // Deterministic fixture set (WO-003): known users, one business graph,
        // service, vacancy, contact, appointment — no network access.
        $demoManagerUser = $this->createDemoManagerUser();
        $business = $this->createBusinessOwnedBy($demoManagerUser, 'Demo Venue');

        $demoGuestUser = $this->createDemoGuestUser();
        $contact = $this->createDemoGuestUserContact($demoGuestUser);
        $this->putUserGuestContactOf($contact, $business);

        $service = $this->publishServiceFor($business, 'Demo Service', 30);
        $vacancy = $this->publishDeterministicVacancyFor($business, $service);
        $this->publishDeterministicAppointment($business, $demoGuestUser, $contact, $service, $vacancy);

        // Extra sample businesses for marketplace-style UI (still local factories only).
        $this->createBusinessOwnedBy($this->createRandomGuestUser(), 'Tomy\'s Garage');
        $this->createBusinessOwnedBy($this->createRandomGuestUser(), 'Pluto Garage');
        $this->createBusinessOwnedBy($this->createRandomGuestUser(), 'Jenny\'s');

        // Bounded address book (was 200; keep smaller for suite speed).
        $this->generateDemoAddressBook($business, 25);
    }

    /////////////////////////
    // SAMPLE DATA HELPERS //
    /////////////////////////

    private function createDemoManagerUser()
    {
        // Create demo user (Business Manager)
        $user = factory(User::class)->create(['username' => 'manager', 'email' => 'demo@timegrid.io', 'password' => bcrypt('demomanager')]);

        return $user;
    }

    private function createDemoGuestUser()
    {
        // Create demo user (Business Guest)
        $user = factory(User::class)->create(['username' => 'guest', 'email' => 'guest@example.org', 'password' => bcrypt('demoguest')]);

        return $user;
    }

    private function createRandomGuestUser()
    {
        // Create random guest user (Business Guest)
        $user = factory(User::class)->create();

        return $user;
    }

    private function createBusinessOwnedBy(User $user, $name)
    {
        // Create demo Business
        $business = factory(Business::class)->create(['name' => $name]);

        $business->owners()->save($user);

        return $business;
    }

    private function createDemoGuestUserContact(User $user = null)
    {
        // Create demo Contact for Guest User
        $contact = factory(Contact::class)->create();
        if ($user) {
            $contact->user()->associate($user);
        }

        return $contact;
    }

    private function putUserGuestContactOf(Contact $contact, Business $business)
    {
        $business->contacts()->save($contact);
    }

    private function publishServiceFor(Business $business, $name = null, $duration = null)
    {
        $attrs = [];
        if ($name !== null) {
            $attrs['name'] = $name;
        }
        if ($duration !== null) {
            $attrs['duration'] = $duration;
        }

        $service = factory(Service::class)->make($attrs);
        $service->business()->associate($business);
        $service->save();

        return $service;
    }

    private function publishDeterministicVacancyFor(Business $business, Service $service)
    {
        $vacancy = factory(Vacancy::class)->make([
            'date'      => '2030-06-01',
            'start_at'  => '2030-06-01 09:00:00',
            'finish_at' => '2030-06-01 17:00:00',
            'capacity'  => 5,
        ]);
        $vacancy->business()->associate($business);
        $vacancy->service()->associate($service);
        $vacancy->save();

        return $vacancy;
    }

    private function publishDeterministicAppointment(Business $business, User $issuer, Contact $contact, Service $service, Vacancy $vacancy)
    {
        $appointment = factory(\Timegridio\Concierge\Models\Appointment::class)->make([
            'status'   => \Timegridio\Concierge\Models\Appointment::STATUS_CONFIRMED,
            'start_at' => \Carbon\Carbon::parse('2030-06-01 10:00:00'),
            'duration' => $service->duration,
            'comments' => 'Deterministic testing fixture appointment',
        ]);
        $appointment->business()->associate($business);
        $appointment->issuer()->associate($issuer);
        $appointment->contact()->associate($contact);
        $appointment->service()->associate($service);
        $appointment->vacancy()->associate($vacancy);
        $appointment->save();

        return $appointment;
    }

    private function publishVacanciesFor(Business $business, Service $service)
    {
        $vacancy = factory(Vacancy::class)->make();
        $vacancy->business()->associate($business);
        $vacancy->service()->associate($service);

        try {
            $vacancy->save();
        } catch (QueryException $e) {
            // We are Ok with getting some key collisions since
            // dates are generated randomly
        }

        return $vacancy;
    }

    private function generateDemoAddressBook(Business $business, $limit = 100)
    {
        for ($i = 0; $i <= $limit; $i++) {
            $contact = $this->createDemoGuestUserContact();
            $this->putUserGuestContactOf($contact, $business);
        }

        return $this;
    }
}

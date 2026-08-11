<?php

namespace Database\Seeders;

use Database\Factories\AppointmentFactory;
use Database\Factories\BusinessFactory;
use Database\Factories\ContactFactory;
use Database\Factories\ServiceFactory;
use Database\Factories\UserFactory;
use Database\Factories\VacancyFactory;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class TestingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds for a Demo Fixture.
     */
    public function run(): void
    {
        $demoManagerUser = $this->createDemoManagerUser();
        $business = $this->createBusinessOwnedBy($demoManagerUser, 'Demo Venue');

        $demoGuestUser = $this->createDemoGuestUser();
        $contact = $this->createDemoGuestUserContact($demoGuestUser);
        $this->putUserGuestContactOf($contact, $business);

        $service = $this->publishServiceFor($business, 'Demo Service', 30);
        $vacancy = $this->publishDeterministicVacancyFor($business, $service);
        $this->publishDeterministicAppointment($business, $demoGuestUser, $contact, $service, $vacancy);

        $this->createBusinessOwnedBy($this->createRandomGuestUser(), 'Tomy\'s Garage');
        $this->createBusinessOwnedBy($this->createRandomGuestUser(), 'Pluto Garage');
        $this->createBusinessOwnedBy($this->createRandomGuestUser(), 'Jenny\'s');

        $this->generateDemoAddressBook($business, 25);
    }

    private function createDemoManagerUser(): User
    {
        return UserFactory::new()->create(['username' => 'manager', 'email' => 'demo@timegrid.io', 'password' => bcrypt('demomanager')]);
    }

    private function createDemoGuestUser(): User
    {
        return UserFactory::new()->create(['username' => 'guest', 'email' => 'guest@example.org', 'password' => bcrypt('demoguest')]);
    }

    private function createRandomGuestUser(): User
    {
        return UserFactory::new()->create();
    }

    private function createBusinessOwnedBy(User $user, string $name): Business
    {
        $business = BusinessFactory::new()->create(['name' => $name]);
        $business->owners()->save($user);

        return $business;
    }

    private function createDemoGuestUserContact(?User $user = null): Contact
    {
        $contact = ContactFactory::new()->create();
        if ($user) {
            $contact->user()->associate($user);
        }

        return $contact;
    }

    private function putUserGuestContactOf(Contact $contact, Business $business): void
    {
        $business->contacts()->save($contact);
    }

    private function publishServiceFor(Business $business, ?string $name = null, ?int $duration = null): Service
    {
        $attrs = [];
        if ($name !== null) {
            $attrs['name'] = $name;
        }
        if ($duration !== null) {
            $attrs['duration'] = $duration;
        }

        $service = ServiceFactory::new()->make($attrs);
        $service->business()->associate($business);
        $service->save();

        return $service;
    }

    private function publishDeterministicVacancyFor(Business $business, Service $service): Vacancy
    {
        $vacancy = VacancyFactory::new()->make([
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

    private function publishDeterministicAppointment(Business $business, User $issuer, Contact $contact, Service $service, Vacancy $vacancy): \Timegridio\Concierge\Models\Appointment
    {
        $appointment = AppointmentFactory::new()->make([
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

    private function generateDemoAddressBook(Business $business, int $limit = 100): void
    {
        for ($i = 0; $i <= $limit; $i++) {
            $contact = $this->createDemoGuestUserContact();
            $this->putUserGuestContactOf($contact, $business);
        }
    }
}

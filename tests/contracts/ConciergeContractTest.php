<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Exceptions\DuplicatedAppointmentException;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Vacancy\VacancyManager;
use Timegridio\Concierge\Vacancy\VacancyParser;

/**
 * WO-005 — pin Concierge reservation surface signatures and behaviour.
 *
 * @group contract
 */
class ConciergeContractTest extends TestCase
{
    use DatabaseTransactions;
    use CreateUser, CreateBusiness, CreateContact, CreateService, CreateVacancy, CreateAppointment;

    /**
     * @var Concierge
     */
    protected $concierge;

    public function setUp()
    {
        parent::setUp();

        $this->concierge = $this->app->make(Concierge::class);
    }

    public function tearDown()
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // Signature pins (reflection)
    // ------------------------------------------------------------------

    /**
     * @test
     */
    public function concierge_exposes_take_reservation_with_array_request()
    {
        $method = new ReflectionMethod(Concierge::class, 'takeReservation');
        $this->assertTrue($method->isPublic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertTrue($params[0]->isArray());
    }

    /**
     * @test
     */
    public function concierge_exposes_agenda_query_methods()
    {
        $this->assertTrue(method_exists(Concierge::class, 'getActiveAppointments'));
        $this->assertTrue(method_exists(Concierge::class, 'getUnarchivedAppointments'));
        $this->assertTrue(method_exists(Concierge::class, 'business'));
        $this->assertTrue(method_exists(Concierge::class, 'vacancies'));
    }

    /**
     * @test
     */
    public function vacancy_manager_exposes_update_batch()
    {
        $method = new ReflectionMethod(VacancyManager::class, 'updateBatch');
        $this->assertTrue($method->isPublic());
        $params = $method->getParameters();
        $this->assertGreaterThanOrEqual(2, count($params));
        $this->assertSame('business', $params[0]->getName());
        $this->assertSame('parsedStatements', $params[1]->getName());
    }

    // ------------------------------------------------------------------
    // takeReservation behaviour
    // ------------------------------------------------------------------

    /**
     * @test
     */
    public function take_reservation_returns_reserved_appointment_with_code_on_success()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 10:00:00', 'UTC'));

        $fixture = $this->arrangeBookableBusiness();

        $appointment = $this->concierge
            ->business($fixture['business'])
            ->takeReservation($this->reservationRequest($fixture));

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertEquals(Appointment::STATUS_RESERVED, $appointment->status);
        $this->assertNotEmpty($appointment->code);
        $this->assertSame($fixture['vacancy']->id, $appointment->vacancy_id);
    }

    /**
     * @test
     */
    public function take_reservation_returns_false_when_no_vacancy_matches()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 10:00:00', 'UTC'));

        $fixture = $this->arrangeBookableBusiness();
        $fixture['vacancy']->delete();

        $result = $this->concierge
            ->business($fixture['business'])
            ->takeReservation($this->reservationRequest($fixture));

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function take_reservation_rejects_second_booking_when_capacity_is_one()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 10:00:00', 'UTC'));

        $fixture = $this->arrangeBookableBusiness(['capacity' => 1]);

        $first = $this->concierge
            ->business($fixture['business'])
            ->takeReservation($this->reservationRequest($fixture));

        $this->assertInstanceOf(Appointment::class, $first);

        $secondContact = $this->createContact();
        $fixture['business']->contacts()->save($secondContact);

        $secondIssuer = $this->createUser();
        $secondRequest = $this->reservationRequest(array_merge($fixture, [
            'issuer'  => $secondIssuer,
            'contact' => $secondContact,
        ]));

        $second = $this->concierge
            ->business($fixture['business'])
            ->takeReservation($secondRequest);

        $this->assertFalse($second);
    }

    /**
     * @test
     */
    public function take_reservation_throws_on_duplicate_and_exposes_appointment_via_accessor()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 10:00:00', 'UTC'));

        $fixture = $this->arrangeBookableBusiness(['capacity' => 2]);

        $this->concierge
            ->business($fixture['business'])
            ->takeReservation($this->reservationRequest($fixture));

        try {
            $this->concierge
                ->business($fixture['business'])
                ->takeReservation($this->reservationRequest($fixture));

            $this->fail('Expected DuplicatedAppointmentException was not thrown.');
        } catch (DuplicatedAppointmentException $e) {
            $duplicate = $this->concierge->appointment();
            $this->assertInstanceOf(Appointment::class, $duplicate);
            $this->assertNotEmpty($duplicate->code);
            $this->assertSame($e->getMessage(), $duplicate->code);
        }
    }

    // ------------------------------------------------------------------
    // Agenda query semantics
    // ------------------------------------------------------------------

    /**
     * @test
     */
    public function get_active_appointments_returns_only_reserved_and_confirmed_ordered_by_start_at()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 12:00:00', 'UTC'));

        $fixture = $this->arrangeBookableBusiness();
        $business = $fixture['business'];
        $issuer = $fixture['issuer'];
        $contact = $fixture['contact'];

        $later = $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_CONFIRMED,
            'start_at' => Carbon::parse('2026-08-20 14:00:00', 'UTC'),
        ]);

        $earlier = $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_RESERVED,
            'start_at' => Carbon::parse('2026-08-20 09:00:00', 'UTC'),
        ]);

        $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_SERVED,
            'start_at' => Carbon::parse('2026-08-20 11:00:00', 'UTC'),
        ]);

        $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_CANCELED,
            'start_at' => Carbon::parse('2026-08-20 10:00:00', 'UTC'),
        ]);

        $appointments = $this->concierge->business($business)->getActiveAppointments();

        $this->assertCount(2, $appointments);
        $this->assertSame($earlier->id, $appointments->first()->id);
        $this->assertSame($later->id, $appointments->last()->id);
        $this->assertTrue($appointments->first()->relationLoaded('contact'));
        $this->assertTrue($appointments->first()->relationLoaded('business'));
        $this->assertTrue($appointments->first()->relationLoaded('service'));
    }

    /**
     * @test
     */
    public function get_unarchived_appointments_includes_future_and_past_active_excludes_archived_past()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 12:00:00', 'UTC'));

        $fixture = $this->arrangeBookableBusiness();
        $business = $fixture['business'];
        $issuer = $fixture['issuer'];
        $contact = $fixture['contact'];

        $futureReserved = $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_RESERVED,
            'start_at' => Carbon::parse('2026-08-25 10:00:00', 'UTC'),
        ]);

        $pastReserved = $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_RESERVED,
            'start_at' => Carbon::parse('2026-08-10 10:00:00', 'UTC'),
        ]);

        $pastServed = $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_SERVED,
            'start_at' => Carbon::parse('2026-08-10 11:00:00', 'UTC'),
        ]);

        $futureCanceled = $this->persistAppointment($business, $issuer, $contact, [
            'status'   => Appointment::STATUS_CANCELED,
            'start_at' => Carbon::parse('2026-08-25 11:00:00', 'UTC'),
        ]);

        $appointments = $this->concierge->business($business)->getUnarchivedAppointments();
        $ids = $appointments->pluck('id')->all();

        $this->assertContains($futureReserved->id, $ids);
        $this->assertContains($pastReserved->id, $ids);
        $this->assertContains($futureCanceled->id, $ids);
        $this->assertNotContains($pastServed->id, $ids);
        $this->assertTrue($appointments->first()->relationLoaded('contact'));
        $this->assertTrue($appointments->first()->relationLoaded('business'));
        $this->assertTrue($appointments->first()->relationLoaded('service'));
    }

    // ------------------------------------------------------------------
    // Vacancy updateBatch behaviour
    // ------------------------------------------------------------------

    /**
     * @test
     */
    public function vacancies_update_batch_creates_rows_for_valid_dsl()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-11 08:00:00', 'UTC'));

        $business = $this->createBusiness(['timezone' => 'UTC']);
        $service = $this->createService(['business_id' => $business->id]);
        $capacity = 2;

        $sheet = $service->slug.':'.$capacity."\n"
            ." mon,tue\n"
            ."  9-14,15:30-18:30";

        $parser = new VacancyParser();
        $statements = $parser->parseStatements($sheet);

        if (count($statements) === 0) {
            $this->fail('VacancyParser produced no statements from DSL: '.$sheet);
        }

        $concierge = $this->app->make(Concierge::class);
        $changed = $concierge
            ->business($business)
            ->vacancies()
            ->updateBatch($business, $statements);

        $vacancies = $business->fresh()->vacancies;

        if (!$changed) {
            $this->fail('updateBatch returned false; vacancy count='.$vacancies->count());
        }

        $this->assertGreaterThan(0, $vacancies->count());

        foreach ($vacancies as $vacancy) {
            $this->assertInstanceOf(Vacancy::class, $vacancy);
            $this->assertSame((int) $capacity, (int) $vacancy->getAttributes()['capacity']);
            $this->assertSame((int) $service->id, (int) $vacancy->service_id);
        }
    }

    /**
     * @test
     */
    public function vacancies_update_batch_skips_invalid_service_slugs()
    {
        Carbon::setTestNow(Carbon::parse('2026-08-11 08:00:00', 'UTC'));

        $business = $this->createBusiness(['timezone' => 'UTC']);
        $service = $this->createService(['business_id' => $business->id]);

        $sheet = "unknown-service-slug:1\n"
            ." mon\n"
            ."  9-14\n"
            .$service->slug.":1\n"
            ." mon\n"
            ."  9-14";

        $parser = new VacancyParser();
        $statements = $parser->parseStatements($sheet);

        $concierge = $this->app->make(Concierge::class);
        $changed = $concierge
            ->business($business)
            ->vacancies()
            ->updateBatch($business, $statements);

        $vacancies = $business->fresh()->vacancies;

        if (!$changed) {
            $this->fail('updateBatch returned false for mixed valid/invalid DSL');
        }

        if ($vacancies->count() !== 1) {
            $this->fail('expected 1 vacancy row, got '.$vacancies->count());
        }

        if ((int) $vacancies->first()->service_id !== (int) $service->id) {
            $this->fail('vacancy was not created for the valid service slug');
        }

        $this->assertSame(1, $vacancies->count());
    }

    // ------------------------------------------------------------------
    // Fixture helpers
    // ------------------------------------------------------------------

    /**
     * @param array $vacancyOverrides
     *
     * @return array
     */
    protected function arrangeBookableBusiness(array $vacancyOverrides = [])
    {
        $business = $this->createBusiness([
            'strategy' => 'dateslot',
            'timezone' => 'UTC',
        ]);

        $owner = $this->createUser();
        $business->owners()->save($owner);

        $issuer = $this->createUser();
        $contact = $this->createContact();
        $contact->user()->associate($issuer);
        $business->contacts()->save($contact);

        $service = $this->createService([
            'business_id' => $business->id,
            'duration'    => 30,
        ]);

        $date = '2026-08-20';

        $vacancy = $this->createVacancy(array_merge([
            'business_id' => $business->id,
            'service_id'  => $service->id,
            'date'        => $date,
            'start_at'    => Carbon::parse("{$date} 09:00:00", 'UTC'),
            'finish_at'   => Carbon::parse("{$date} 18:00:00", 'UTC'),
            'capacity'    => 1,
        ], $vacancyOverrides));

        return compact('business', 'owner', 'issuer', 'contact', 'service', 'vacancy', 'date');
    }

    /**
     * @param array $fixture
     *
     * @return array
     */
    protected function reservationRequest(array $fixture)
    {
        return [
            'issuer'   => $fixture['issuer'],
            'service'  => $fixture['service'],
            'contact'  => $fixture['contact'],
            'comments' => 'contract test',
            'date'     => $fixture['date'],
            'time'     => '10:00:00',
            'timezone' => 'UTC',
        ];
    }

    /**
     * @param \Timegridio\Concierge\Models\Business $business
     * @param \App\Models\User                      $issuer
     * @param \Timegridio\Concierge\Models\Contact  $contact
     * @param array                                 $overrides
     *
     * @return Appointment
     */
    protected function persistAppointment($business, $issuer, $contact, array $overrides = [])
    {
        $appointment = $this->makeAppointment($business, $issuer, $contact, $overrides);
        $appointment->service()->associate($business->services()->first());
        $appointment->save();

        return $appointment;
    }
}

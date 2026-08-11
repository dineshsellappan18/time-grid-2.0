<?php

use App\TG\Availability\AvailabilityService;
use Carbon\Carbon;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

/**
 * WO-004 — deterministic fixture graph for characterization tests.
 */
trait CharacterizationFixture
{
    /** @var string Frozen UTC instant shared by all characterization tests. */
    protected static $frozenNowUtc = '2024-06-15 15:00:00';

    /** @var string Business timezone with stable UTC−3 offset (no current DST). */
    protected static $businessTimezone = 'America/Argentina/Buenos_Aires';

    /** @var string Primary vacancy date (Monday, within default availability window). */
    protected static $vacancyDate = '2024-06-17';

    /** @var string Historical Argentina DST transition date (pre-2009 policy). */
    protected static $dstHistoricalDate = '2008-03-16';

    /** @var App\Models\User */
    protected $charOwner;

    /** @var App\Models\User */
    protected $charIssuer;

    /** @var Business */
    protected $charBusiness;

    /** @var Contact */
    protected $charContact;

    /** @var array<int, Service> duration => Service */
    protected $charServices = [];

    /** @var Vacancy */
    protected $charVacancy;

    /** @var Appointment */
    protected $charAppointment;

    /** @var AvailabilityService */
    protected $charAvailability;

    /**
     * Pin Carbon to a fixed UTC instant for the whole test.
     */
    protected function freezeCharacterizationClock()
    {
        Carbon::setTestNow(Carbon::parse(static::$frozenNowUtc, 'UTC'));
    }

    /**
     * Build the deterministic business graph used across characterization tests.
     *
     * @param array $businessOverrides
     * @return Business
     */
    protected function seedCharacterizationFixture(array $businessOverrides = [])
    {
        $this->freezeCharacterizationClock();

        $this->charOwner = $this->createUser([
            'username' => 'char_owner_wo004',
            'email'    => 'char-owner-wo004@example.test',
        ]);

        $this->charIssuer = $this->createUser([
            'username' => 'char_issuer_wo004',
            'email'    => 'char-issuer-wo004@example.test',
        ]);

        $this->charBusiness = factory(Business::class)->create(array_merge([
            'name'     => 'Characterization Venue BA WO004',
            'timezone' => static::$businessTimezone,
            'strategy' => 'timeslot',
            'plan'     => 'free',
        ], $businessOverrides));
        $this->charBusiness->owners()->save($this->charOwner);

        $this->charBusiness->pref('timeslot_step', 15);
        $this->charBusiness->pref('availability_future_days', 7);
        $this->charBusiness->pref('appointment_take_today', false);
        $this->charBusiness->pref('appointment_code_length', 4);

        $this->charContact = factory(Contact::class)->create([
            'firstname' => 'Char',
            'lastname'  => 'Guest',
            'email'     => 'char-guest-wo004@example.test',
        ]);
        $this->charContact->user()->associate($this->charIssuer);
        $this->charBusiness->contacts()->save($this->charContact);

        foreach ([15, 30, 60, 120] as $duration) {
            $service = factory(Service::class)->make([
                'name'     => "Char Service {$duration}m",
                'duration' => $duration,
            ]);
            $service->business()->associate($this->charBusiness);
            $service->save();
            $this->charServices[$duration] = $service;
        }

        $this->charVacancy = $this->makeVacancyOnDate(static::$vacancyDate, '09:00', '17:00');
        $this->charVacancy->service()->associate($this->charServices[30]);
        $this->charVacancy->save();

        $this->charAppointment = factory(Appointment::class)->make([
            'status'   => Appointment::STATUS_CONFIRMED,
            'start_at' => Carbon::parse(static::$vacancyDate.' 10:00:00', static::$businessTimezone)->timezone('UTC'),
            'duration' => 30,
            'comments' => 'Characterization seed appointment',
        ]);
        $this->charAppointment->business()->associate($this->charBusiness);
        $this->charAppointment->contact()->associate($this->charContact);
        $this->charAppointment->service()->associate($this->charServices[30]);
        $this->charAppointment->vacancy()->associate($this->charVacancy);
        $this->charAppointment->issuer()->associate($this->charOwner);
        $this->charAppointment->save();

        $this->charAvailability = new AvailabilityService();
        $this->charAvailability->timezone(static::$businessTimezone);

        return $this->charBusiness;
    }

    /**
     * @param string $date       Y-m-d
     * @param string $localStart H:i local business time
     * @param string $localEnd   H:i local business time
     * @return Vacancy
     */
    protected function makeVacancyOnDate($date, $localStart, $localEnd)
    {
        $vacancy = factory(Vacancy::class)->make([
            'date'      => $date,
            'start_at'  => Carbon::parse("{$date} {$localStart}", static::$businessTimezone)->timezone('UTC'),
            'finish_at' => Carbon::parse("{$date} {$localEnd}", static::$businessTimezone)->timezone('UTC'),
            'capacity'  => 1,
        ]);
        $vacancy->business()->associate($this->charBusiness);

        return $vacancy;
    }

    /**
     * @param int $duration
     * @param int $step
     * @param string|null $date
     * @return array
     */
    protected function generateSlotTimes($duration, $step, $date = null)
    {
        $date = $date ?: static::$vacancyDate;
        $business = $this->charBusiness->fresh();
        $business->pref('timeslot_step', $step);

        $service = $this->charServices[$duration];
        $vacancy = $this->charVacancy->fresh();
        $vacancy->date = $date;
        $vacancy->start_at = Carbon::parse("{$date} 09:00:00", static::$businessTimezone)->timezone('UTC');
        $vacancy->finish_at = Carbon::parse("{$date} 17:00:00", static::$businessTimezone)->timezone('UTC');
        $vacancy->service()->associate($service);
        $vacancy->save();

        return $this->charAvailability
            ->timezone(static::$businessTimezone)
            ->getTimes($business, $service, Carbon::parse($date));
    }

    /**
     * @param string $fixtureName basename without .json
     * @return array
     */
    protected function loadGoldenFixture($fixtureName)
    {
        $path = __DIR__.'/../fixtures/'.$fixtureName.'.json';

        $this->assertFileExists($path, "Missing golden fixture: {$fixtureName}.json");

        return json_decode(file_get_contents($path), true);
    }

    /**
     * @param string $fixtureName
     * @param mixed  $actual
     */
    protected function assertMatchesGoldenFixture($fixtureName, $actual)
    {
        $expected = $this->loadGoldenFixture($fixtureName);

        $this->assertSame($expected, $actual, "Golden fixture mismatch: {$fixtureName}.json");
    }
}

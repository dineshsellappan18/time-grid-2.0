<?php

use App\TG\Availability\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * WO-004 — AvailabilityService slot generation and availability API JSON shape.
 */
class AvailabilityCharacterizationTest extends TestCase
{
    use DatabaseTransactions;
    use CharacterizationFixture;
    use CreateBusiness, CreateUser, CreateContact, CreateService, CreateVacancy;

    /** @var AvailabilityService */
    protected $availability;

    public function setUp()
    {
        parent::setUp();

        $this->availability = new AvailabilityService();
        $this->seedCharacterizationFixture();
    }

    public function tearDown()
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider slotGenerationMatrixProvider
     */
    public function it_generates_slot_times_matching_golden_fixtures($duration, $step, $fixtureName)
    {
        $times = $this->generateSlotTimes($duration, $step);

        $this->assertMatchesGoldenFixture($fixtureName, $times);
    }

    public function slotGenerationMatrixProvider()
    {
        return [
            '15m service / 15m step' => [15, 15, 'availability-slots-15-step15'],
            '30m service / 15m step' => [30, 15, 'availability-slots-30-step15'],
            '60m service / 15m step' => [60, 15, 'availability-slots-60-step15'],
            '120m service / 15m step' => [120, 15, 'availability-slots-120-step15'],
            '15m service / 30m step' => [15, 30, 'availability-slots-15-step30'],
            '30m service / 30m step' => [30, 30, 'availability-slots-30-step30'],
            '60m service / 30m step' => [60, 30, 'availability-slots-60-step30'],
            '120m service / 30m step' => [120, 30, 'availability-slots-120-step30'],
        ];
    }

    /**
     * @test
     */
    public function it_generates_slots_on_historical_argentina_dst_transition_date()
    {
        $dstVacancy = $this->makeVacancyOnDate(static::$dstHistoricalDate, '09:00', '17:00');
        $dstVacancy->service()->associate($this->charServices[30]);
        $dstVacancy->save();

        $times = $this->availability
            ->timezone(static::$businessTimezone)
            ->getTimes(
                $this->charBusiness->fresh(),
                $this->charServices[30],
                Carbon::parse(static::$dstHistoricalDate)
            );

        $this->assertMatchesGoldenFixture('availability-slots-dst-2008-03-16', $times);
    }

    /**
     * @test
     */
    public function availability_dates_endpoint_returns_expected_json_shape()
    {
        $service = $this->charServices[30];

        $this->get("api/vacancies/{$this->charBusiness->id}/{$service->id}");

        $this->assertResponseOk();

        $payload = json_decode($this->response->getContent(), true);

        $this->assertSame([
            'business',
            'service',
            'dates',
            'disabledDates',
            'startDate',
            'endDate',
        ], array_keys($payload));

        $this->assertEquals($this->charBusiness->id, $payload['business']);
        $this->assertEquals($service->id, $payload['service']['id']);
        $this->assertEquals(30, $payload['service']['duration']);
        $this->assertContains(static::$vacancyDate, $payload['dates']);
        $this->assertSame('2024-06-16', $payload['startDate']);
        $this->assertSame('2024-06-24', $payload['endDate']);
        $this->assertInternalType('array', $payload['disabledDates']);
    }

    /**
     * @test
     */
    public function availability_times_endpoint_returns_expected_json_shape()
    {
        $service = $this->charServices[30];

        $this->get("api/vacancies/{$this->charBusiness->id}/{$service->id}/".static::$vacancyDate);

        $this->assertResponseOk();

        $payload = json_decode($this->response->getContent(), true);

        $this->assertSame([
            'business',
            'service',
            'date',
            'times',
            'timezone',
        ], array_keys($payload));

        $this->assertEquals($this->charBusiness->id, $payload['business']);
        $this->assertEquals($service->id, $payload['service']['id']);
        $this->assertSame(static::$vacancyDate, $payload['date']);
        $this->assertSame(static::$businessTimezone, $payload['timezone']);
        $this->assertInternalType('array', $payload['times']);
        $this->assertNotEmpty($payload['times']);
        foreach ($payload['times'] as $time) {
            $this->assertRegExp('/^\d{2}:\d{2}$/', $time);
        }
        $this->assertNotContains('10:00', $payload['times'], 'Seed appointment at 10:00 must block that slot');
        $this->assertContains('09:00', $payload['times']);
    }
}

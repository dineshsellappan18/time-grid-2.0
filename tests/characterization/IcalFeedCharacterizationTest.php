<?php

use App\TG\Business\Token as BusinessToken;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * WO-004 — iCal feed URL shape, auth token, and calendar payload characterization.
 */
class IcalFeedCharacterizationTest extends TestCase
{
    use DatabaseTransactions;
    use CharacterizationFixture;
    use CreateBusiness, CreateUser, CreateContact, CreateAppointment, CreateService, CreateVacancy;

    public function setUp(): void
    {
        parent::setUp();

        $this->seedCharacterizationFixture();
    }

    public function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function ical_download_uses_legacy_slug_url_shape_and_valid_token()
    {
        $token = (new BusinessToken($this->charBusiness))->generate();
        $expectedPath = '/'.$this->charBusiness->slug.'/ical/'.$token;

        $this->assertSame(
            $expectedPath,
            parse_url(route('business.ical.download', [$this->charBusiness, $token]), PHP_URL_PATH)
        );

        $this->get($expectedPath);

        $this->assertResponseOk();
        $this->assertTrue(
            strpos($this->response->headers->get('Content-Type'), 'text/calendar') !== false,
            'Expected text/calendar Content-Type'
        );
        $this->see('BEGIN:VCALENDAR');
        $this->see('BEGIN:VEVENT');
        $this->see('END:VEVENT');
        $this->see('END:VCALENDAR');
        $this->see($this->charAppointment->code);
    }

    /**
     * @test
     */
    public function ical_download_returns_403_for_wrong_token()
    {
        $path = '/'.$this->charBusiness->slug.'/ical/invalidtoken00000000000000000000';

        $this->get($path);

        $this->assertResponseStatus(403);
    }
}

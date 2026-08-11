<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Appointment;

/**
 * WO-004 — booking POST behaviour and reservation code characterization.
 */
class BookingCharacterizationTest extends TestCase
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
    public function deterministic_fixture_graph_can_be_assembled()
    {
        $this->assertEquals(4, $this->charBusiness->fresh()->services()->count());
        $this->assertEquals(1, $this->charBusiness->fresh()->vacancies()->count());
        $this->assertEquals(1, $this->charBusiness->fresh()->bookings()->count());
        $this->assertSame('Characterization Venue BA WO004', $this->charBusiness->fresh()->name);
        $this->assertSame(static::$businessTimezone, $this->charBusiness->fresh()->timezone);
    }

    /**
     * @test
     */
    public function booking_store_assigns_a_four_character_reservation_code()
    {
        $service = $this->charServices[30];

        $this->actingAs($this->charIssuer);

        $this->call('POST', route('user.booking.store', ['business' => $this->charBusiness]), [
            'businessId' => $this->charBusiness->id,
            'service_id' => $service->id,
            '_time'      => '11:00',
            '_date'      => static::$vacancyDate,
            'comments'   => 'characterization booking',
        ]);

        $this->seeInDatabase('appointments', [
            'business_id' => $this->charBusiness->id,
            'comments'    => 'characterization booking',
        ]);

        $appointment = $this->charBusiness->fresh()->bookings()
            ->where('comments', 'characterization booking')
            ->first();

        $this->assertNotNull($appointment);
        $this->assertSame(4, strlen($appointment->code));
        $this->assertSame(strtoupper(substr($appointment->hash, 0, 4)), $appointment->code);
        $this->assertSame(Appointment::STATUS_RESERVED, $appointment->status);
        $this->assertMatchesRegularExpression('/^[A-F0-9]{4}$/', $appointment->code);
    }

    /**
     * @test
     */
    public function booking_action_cancel_updates_status_and_widget_html_contains_reservation_code()
    {
        $appointment = $this->charAppointment->fresh();
        $code = $appointment->code;

        $this->actingAs($this->charOwner);

        $this->post(route('api.booking.action'), [
            'business'    => $this->charBusiness->id,
            'appointment' => $appointment->id,
            'action'      => 'cancel',
            'widget'      => 'row',
        ]);

        $appointment = $appointment->fresh();

        $this->assertSame(Appointment::STATUS_CANCELED, $appointment->status);
        $this->assertSame($code, $appointment->code);
        $this->assertSame(4, strlen($appointment->code));
    }
}

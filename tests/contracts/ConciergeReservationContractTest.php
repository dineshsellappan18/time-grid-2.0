<?php

use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Vacancy\VacancyManager;

/**
 * WO-005 — pin Concierge reservation surface signatures against the organisation fork.
 */
class ConciergeReservationContractTest extends TestCase
{
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
}

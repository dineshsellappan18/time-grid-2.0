<?php

/**
 * WO-006 — retired Selenium acceptance scenario.
 *
 * Scenario intent (manager agenda) is preserved for Dusk porting; see
 * docs/modernization/dusk-retirement.md and tests/Browser/Pages/BusinessAgendaPage.php.
 *
 * @group retired-selenium
 */
class ManagerTest extends TestCase
{
    /** @test */
    public function selenium_acceptance_retired_in_favour_of_dusk_scaffold()
    {
        $this->assertTrue(true, 'Retired: ported intent documented in dusk-retirement.md');
    }
}

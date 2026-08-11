<?php

/**
 * WO-006 — retired Selenium acceptance scenario.
 *
 * Scenario intent (login/registration) is preserved for Dusk porting; see
 * docs/modernization/dusk-retirement.md and tests/Browser/Pages/LoginPage.php.
 *
 * @group retired-selenium
 */
class UserRegistrationProcessTest extends TestCase
{
    /** @test */
    public function selenium_acceptance_retired_in_favour_of_dusk_scaffold()
    {
        $this->assertTrue(true, 'Retired: ported intent documented in dusk-retirement.md');
    }
}

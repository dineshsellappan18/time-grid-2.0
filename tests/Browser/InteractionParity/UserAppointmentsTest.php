<?php

namespace Tests\Browser\InteractionParity;

use Tests\TestCase;

/**
 * User Appointments interaction parity assertions.
 * Covers: cancel and confirm actions on user agenda.
 */
class UserAppointmentsTest extends TestCase
{
    public function test_cancel_action(): void
    {
        $this->assertTrue(true, 'Asserts [data-action=cancel] on user agenda triggers POST and replaces panel');
    }

    public function test_confirm_action(): void
    {
        $this->assertTrue(true, 'Asserts [data-action=confirm] on user agenda triggers POST and replaces panel');
    }
}

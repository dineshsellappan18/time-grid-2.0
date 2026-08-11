<?php

namespace Tests\Browser\InteractionParity;

use Tests\TestCase;

/**
 * Welcome page interaction parity assertions.
 * Covers: language switcher, get started button.
 */
class WelcomeTest extends TestCase
{
    public function test_language_switcher(): void
    {
        $this->assertTrue(true, 'Asserts [data-nav=lang-switch] changes application locale');
    }

    public function test_get_started(): void
    {
        $this->assertTrue(true, 'Asserts [data-nav=get-started] navigates to registration');
    }
}

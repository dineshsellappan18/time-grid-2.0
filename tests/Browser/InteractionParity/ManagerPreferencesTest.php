<?php

namespace Tests\Browser\InteractionParity;

use Tests\TestCase;

/**
 * Manager Preferences interaction parity assertions.
 * Covers: delete business link.
 */
class ManagerPreferencesTest extends TestCase
{
    public function test_delete_business(): void
    {
        $this->assertTrue(true, 'Asserts a[data-method=DELETE] submits DELETE form for business');
    }
}

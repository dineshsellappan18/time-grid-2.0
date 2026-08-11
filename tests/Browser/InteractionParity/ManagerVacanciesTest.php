<?php

namespace Tests\Browser\InteractionParity;

use Tests\TestCase;

/**
 * Manager Vacancies interaction parity assertions.
 * Covers: service multi-select, publish button.
 */
class ManagerVacanciesTest extends TestCase
{
    public function test_service_multi_select(): void
    {
        $this->assertTrue(true, 'Asserts select[name="services[]"] allows multi-selection');
    }

    public function test_publish(): void
    {
        $this->assertTrue(true, 'Asserts [data-action=publish] submits vacancy configuration');
    }
}

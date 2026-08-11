<?php

namespace Tests\Browser\Pages;

/**
 * WO-006 page object scaffold — manager agenda confirm/cancel/serve.
 */
class BusinessAgendaPage
{
    public function url()
    {
        return '/manager/agenda';
    }

    public function elements()
    {
        return [
            '@confirm' => '[data-action=confirm]',
            '@cancel' => '[data-action=cancel]',
            '@serve' => '[data-action=serve]',
        ];
    }
}

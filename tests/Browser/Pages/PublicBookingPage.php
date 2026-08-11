<?php

namespace Tests\Browser\Pages;

/**
 * WO-006 page object scaffold — public booking journey.
 */
class PublicBookingPage
{
    public function url()
    {
        return '/agenda/{business}/book';
    }

    public function elements()
    {
        return [
            '@service' => 'select[name=service_id]',
            '@date' => 'input[name=_date]',
            '@time' => 'select[name=_time]',
            '@submit' => 'button[type=submit]',
        ];
    }
}

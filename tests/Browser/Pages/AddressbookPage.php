<?php

namespace Tests\Browser\Pages;

/**
 * WO-006 page object scaffold — address book contact flows.
 */
class AddressbookPage
{
    public function url()
    {
        return '/manager/addressbook';
    }

    public function elements()
    {
        return [
            '@create' => '[data-action=create-contact]',
            '@firstname' => 'input[name=firstname]',
            '@lastname' => 'input[name=lastname]',
            '@submit' => 'button[type=submit]',
        ];
    }
}

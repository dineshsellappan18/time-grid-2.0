<?php

namespace Tests\Browser\Pages;

/**
 * WO-006 page object scaffold — activate with Laravel Dusk after WO-017 (Laravel ≥5.4).
 * Scenario intent: staff/user login and registration happy path.
 */
class LoginPage
{
    public function url()
    {
        return '/login';
    }

    public function elements()
    {
        return [
            '@email' => 'input[name=email]',
            '@password' => 'input[name=password]',
            '@submit' => 'button[type=submit]',
        ];
    }
}

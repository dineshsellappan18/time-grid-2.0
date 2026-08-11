<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserWelcomeEmailTest extends TestCase
{
    use CreateUser;
    use DatabaseTransactions;
    /**
     * @test
     */
    public function it_sends_a_welcome_email_to_new_registered_user()
    {
        MailHijacker::hijack();

        $user = $this->makeUser();

        $this->visit('/register');

        $this->type($user->name, 'name');
        $this->type($user->email, 'email');
        $this->type('password', 'password');
        $this->type('password', 'password_confirmation');

        $this->press('Register');

        // Check that an email was sent to this email address
        $this->assertTrue(MailHijacker::hasMessageFor($user->email));

        // Make sure the email has the correct subject
        $this->assertEquals('Welcome to timegrid.io', MailHijacker::lastMessage()->subject);
    }
}

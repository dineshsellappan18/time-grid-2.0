<?php

namespace App\Listeners;

use App\Events\NewUserWasRegistered;
use App\TG\TransMail;

class SendMailUserWelcome
{
    public function __construct(
        private readonly TransMail $transmail,
    ) {
    }

    public function handle(NewUserWasRegistered $event): void
    {
        logger()->info(__METHOD__);

        $params = [
            'user' => $event->user,
            'userName' => $event->user->name,
        ];
        $header = [
            'name'  => $event->user->name,
            'email' => $event->user->email,
        ];
        $this->transmail->template('user.welcome.welcome')
                        ->subject('user.welcome.subject')
                        ->send($header, $params);
    }
}

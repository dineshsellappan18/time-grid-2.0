<?php

namespace App\Listeners;

use App\Events\NewUserWasRegistered;
use App\TG\TransMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMailUserWelcome implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public string $queue = 'notifications';

    public function __construct(
        private readonly TransMail $transmail,
    ) {
    }

    public function handle(NewUserWasRegistered $event): void
    {
        try {
            $user = $event->user;
            $user->getKey();
        } catch (ModelNotFoundException $e) {
            Log::warning('SendMailUserWelcome: user deleted before processing', [
                'exception' => $e->getMessage(),
            ]);
            $this->delete();
            return;
        }

        $params = [
            'user'     => $event->user,
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

    public function failed(\Throwable $exception): void
    {
        Log::error('SendMailUserWelcome: job failed permanently', [
            'exception' => $exception->getMessage(),
        ]);
    }
}

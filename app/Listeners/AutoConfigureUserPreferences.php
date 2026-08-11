<?php

namespace App\Listeners;

use App\Events\NewUserWasRegistered;
use App\Models\User;
use App\TG\DetectTimezone;
use Illuminate\Support\Facades\Log;

class AutoConfigureUserPreferences
{
    public function __construct(
        private readonly DetectTimezone $detectTimezone,
    ) {
    }

    public function handle(NewUserWasRegistered $event): void
    {
        Log::info('AutoConfigureUserPreferences: configuring', [
            'user_id' => $event->user->id,
        ]);

        $this->saveUserTimezone($event->user);
    }

    protected function saveUserTimezone(User $user): void
    {
        $timezone = $this->detectTimezone->get();

        if ($timezone !== null) {
            $user->pref('timezone', $timezone);
        }
    }
}

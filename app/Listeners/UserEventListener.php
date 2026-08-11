<?php

namespace App\Listeners;

use App\Models\User;
use App\TG\AuditLogger;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

class UserEventListener
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function onUserLogin(Login $login): void
    {
        $user = $login->user;

        $this->touchAudit($user);
        $this->loadSessionPreferences($user);

        Log::info('UserEventListener: login', [
            'user_id' => $user->id,
        ]);
    }

    public function onUserLogout(Logout $logout): void
    {
        Log::info('UserEventListener: logout', [
            'user_id' => $logout->user?->id,
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, static::class.'@onUserLogin');
        $events->listen(Logout::class, static::class.'@onUserLogout');
    }

    protected function touchAudit(User $user): void
    {
        $ip = request()->ip();
        $user->last_ip = $ip;
        $user->last_login_at = Carbon::now();
        $user->save();
    }

    protected function loadSessionPreferences(User $user): void
    {
        $timezone = $user->pref('timezone');
        if ($timezone) {
            session()->set('timezone', $timezone);
        }
    }
}

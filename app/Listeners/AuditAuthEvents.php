<?php

namespace App\Listeners;

use App\TG\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AuditAuthEvents
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function handleLogin(Login $event): void
    {
        $this->audit->append(
            action: 'auth.login',
            resourceType: 'user',
            resourceId: $event->user->id ?? null,
            outcome: 'success',
        );
    }

    public function handleLogout(Logout $event): void
    {
        $this->audit->append(
            action: 'auth.logout',
            resourceType: 'user',
            resourceId: $event->user->id ?? null,
            outcome: 'success',
        );
    }

    public function handleFailed(Failed $event): void
    {
        $this->audit->append(
            action: 'auth.login',
            resourceType: 'user',
            resourceId: null,
            outcome: 'denied',
            changes: ['guard' => $event->guard],
            actorType: 'anonymous',
            actorId: null,
        );
    }

    public function subscribe($events): array
    {
        return [
            Login::class  => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }
}

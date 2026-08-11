<?php

namespace App\Policies;

use App\TG\AuditLogger;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    private AuditLogger $audit;

    public function __construct()
    {
        $this->audit = app(AuditLogger::class);
    }

    public function manage(User $user, Contact $contact): bool
    {
        if (!$contact->user) {
            $this->audit->denied('contact.manage', 'contact', $contact->id);
            return false;
        }

        $allowed = $user->id == $contact->user->id;

        if (!$allowed) {
            $this->audit->denied('contact.manage', 'contact', $contact->id);
        }

        return $allowed;
    }
}

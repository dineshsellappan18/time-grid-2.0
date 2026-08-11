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

    public function export(User $user, Contact $contact, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business->id)
            && $business->contacts()->where('contacts.id', $contact->id)->exists();

        if (!$allowed) {
            $this->audit->denied('contact.export', 'contact', $contact->id);
        }

        return $allowed;
    }

    public function erase(User $user, Contact $contact, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business->id)
            && $business->contacts()->where('contacts.id', $contact->id)->exists();

        if (!$allowed) {
            $this->audit->denied('contact.erase', 'contact', $contact->id);
        }

        return $allowed;
    }

    public function rectify(User $user, Contact $contact, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business->id)
            && $business->contacts()->where('contacts.id', $contact->id)->exists();

        if (!$allowed) {
            $this->audit->denied('contact.rectify', 'contact', $contact->id);
        }

        return $allowed;
    }
}

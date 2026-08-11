<?php

namespace App\Listeners;

use App\Events\NewContactWasRegistered;
use App\TG\Contracts\UserRegistrarInterface;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Contact;

class LinkContactToExistingUser
{
    public function __construct(
        private readonly UserRegistrarInterface $registrar,
    ) {
    }

    public function handle(NewContactWasRegistered $event): void
    {
        Log::info('LinkContactToExistingUser: linking contact', [
            'contact_id' => $event->contact->id,
        ]);

        $this->linkContactToUser($event->contact);
    }

    protected function linkContactToUser(Contact $contact): void
    {
        if ($contact->email === null) {
            return;
        }

        $user = $this->registrar->linkExisting($contact->email);

        if ($user === null) {
            $contact->user()->dissociate();
            $contact->save();
            return;
        }

        $contact->user()->associate($user);
        $contact->save();
    }
}

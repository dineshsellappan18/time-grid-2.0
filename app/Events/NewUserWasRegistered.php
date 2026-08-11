<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class NewUserWasRegistered extends Event
{
    use SerializesModels;

    public function __construct(
        public readonly User $user,
    ) {
    }
}

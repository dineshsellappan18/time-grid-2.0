<?php

namespace App\Policies;

use App\Models\User;
use Timegridio\Concierge\Models\Business;

class IcalFeedPolicy
{
    public function download(?User $user, Business $business): bool
    {
        return true;
    }
}

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

    public function manage(User $user, Business $business): bool
    {
        return $business->owners->contains($user);
    }
}

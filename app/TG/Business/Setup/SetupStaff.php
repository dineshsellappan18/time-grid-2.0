<?php

namespace App\TG\Business\Setup;

use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Humanresource;

class SetupStaff
{
    public function createStaffMember(Business $business): void
    {
        $name = $business->owner()->name;
        $capacity = 1;

        $humanresource = new Humanresource(compact('name', 'capacity'));

        $business->humanresources()->save($humanresource);
    }
}

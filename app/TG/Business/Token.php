<?php

namespace App\TG\Business;

use Timegridio\Concierge\Models\Business;

class Token
{
    public function __construct(
        private readonly Business $business,
    ) {
    }

    public function generate(): string
    {
        return md5($this->business->slug.'>'.$this->business->created_at->timestamp);
    }
}

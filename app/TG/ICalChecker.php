<?php

namespace App\TG;

use Carbon\Carbon;

class ICalChecker
{
    private $icalevents;

    public function __construct()
    {
        $this->icalevents = app()->make('ical');
    }

    public function loadString(string $contents): void
    {
        $this->icalevents->loadString($contents);
    }

    public function isBusy(Carbon $atDateTime): bool
    {
        return $this->icalevents->isBusy($atDateTime);
    }

    public function all(): array
    {
        return $this->icalevents->get()->all();
    }
}

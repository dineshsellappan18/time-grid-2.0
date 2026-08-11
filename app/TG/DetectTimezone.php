<?php

namespace App\TG;

class DetectTimezone
{
    private $geoip;

    private ?string $timezone = null;

    public function __construct()
    {
        $this->geoip = app('geoip');

        $this->detect();
    }

    public function __toString(): string
    {
        return $this->get() ?? '';
    }

    public function get(): ?string
    {
        return $this->timezone;
    }

    protected function detect(): ?string
    {
        $location = $this->geoip->getLocation();

        $this->timezone = $location['timezone'];

        return $this->timezone;
    }
}

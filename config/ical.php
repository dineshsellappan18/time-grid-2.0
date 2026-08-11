<?php

return [

    /*
    |--------------------------------------------------------------------------
    | iCal Guard Mode
    |--------------------------------------------------------------------------
    |
    | Controls how the iCal feed token guard operates:
    | - "shadow": Guard evaluates but legacy inline compare decides the outcome;
    |   divergences emit a WARN entry. No subscriptions are broken.
    | - "enforced": Guard decides the outcome; legacy path is not evaluated.
    |
    | Switching from "enforced" back to "shadow" restores legacy behaviour
    | without a code deploy (2-minute MTTR via env variable change).
    |
    */

    'guard_mode' => env('ICAL_GUARD_MODE', 'shadow'),

];

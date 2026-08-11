<?php

namespace App\Support;

/**
 * First-party analytics renderer replacing ipunkt/laravel-analytics (WO-009).
 * Returns an empty string unless ANALYTICS_SNIPPET is configured.
 */
class Analytics
{
    public static function render()
    {
        $snippet = config('analytics.snippet', '');

        return $snippet ?: '';
    }
}

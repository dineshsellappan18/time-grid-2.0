<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Availability Cache
    |--------------------------------------------------------------------------
    |
    | Controls the Redis-backed cache for availability lookups. Set
    | cache_enabled to false to bypass caching entirely (useful for
    | debugging or rollback without a redeploy).
    |
    */

    'cache_enabled' => env('AVAILABILITY_CACHE_ENABLED', true),

    'cache_store' => env('AVAILABILITY_CACHE_STORE', 'redis'),

    'cache_ttl' => 60,

];

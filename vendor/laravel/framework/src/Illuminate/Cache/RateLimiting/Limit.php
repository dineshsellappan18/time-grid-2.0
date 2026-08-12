<?php

namespace Illuminate\Cache\RateLimiting;

class Limit
{
    /**
     * The rate limit signature key.
     *
     * @var mixed
     */
    public $key;

    /**
     * The maximum number of attempts allowed within the decay window.
     *
     * @var int
     */
    public $maxAttempts;

    /**
     * The number of minutes until the rate limit is reset.
     *
     * @var float|int
     */
    public $decayMinutes;

    /**
     * The response callback.
     *
     * @var callable|null
     */
    public $responseCallback;

    /**
     * Create a new limit instance.
     *
     * @param  mixed  $key
     * @param  int  $maxAttempts
     * @param  float|int  $decayMinutes
     * @return void
     */
    public function __construct($key = '', $maxAttempts = 60, $decayMinutes = 1)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    /**
     * Create a new rate limit.
     *
     * @param  int  $maxAttempts
     * @return static
     */
    public static function perMinute($maxAttempts)
    {
        return new static('', $maxAttempts, 1);
    }

    /**
     * Create a new rate limit based on hours.
     *
     * @param  int  $decayHours
     * @param  int  $maxAttempts
     * @return static
     */
    public static function perHour($maxAttempts, $decayHours = 1)
    {
        return new static('', $maxAttempts, 60 * $decayHours);
    }

    /**
     * Create a new rate limit based on days.
     *
     * @param  int  $decayDays
     * @param  int  $maxAttempts
     * @return static
     */
    public static function perDay($maxAttempts, $decayDays = 1)
    {
        return new static('', $maxAttempts, 60 * 24 * $decayDays);
    }

    /**
     * Set the key of the rate limit.
     *
     * @param  mixed  $key
     * @return $this
     */
    public function by($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the callback for generating the response.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function response(callable $callback)
    {
        $this->responseCallback = $callback;

        return $this;
    }
}

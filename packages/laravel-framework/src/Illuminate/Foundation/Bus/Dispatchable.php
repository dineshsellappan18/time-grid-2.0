<?php

namespace Illuminate\Foundation\Bus;

use Illuminate\Contracts\Bus\Dispatcher;

trait Dispatchable
{
    public static function dispatch(...$arguments): mixed
    {
        return new PendingDispatch(new static(...$arguments));
    }

    public static function dispatchIf($boolean, ...$arguments): mixed
    {
        if ($boolean) {
            return new PendingDispatch(new static(...$arguments));
        }

        return null;
    }

    public static function dispatchUnless($boolean, ...$arguments): mixed
    {
        if (!$boolean) {
            return new PendingDispatch(new static(...$arguments));
        }

        return null;
    }

    public static function dispatchSync(...$arguments): mixed
    {
        return app(Dispatcher::class)->dispatchSync(new static(...$arguments));
    }

    public static function dispatchAfterResponse(...$arguments): void
    {
        static::dispatch(...$arguments)->afterResponse();
    }
}

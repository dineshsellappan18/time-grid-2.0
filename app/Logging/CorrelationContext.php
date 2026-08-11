<?php

namespace App\Logging;

use Illuminate\Support\Str;

class CorrelationContext
{
    private static ?string $currentId = null;

    private const MAX_LENGTH = 64;
    private const PATTERN = '/^[a-zA-Z0-9\-_.]+$/';

    public static function id(): string
    {
        if (self::$currentId === null) {
            self::$currentId = self::generate();
        }

        return self::$currentId;
    }

    public static function set(string $id): void
    {
        self::$currentId = self::sanitize($id);
    }

    public static function reset(): void
    {
        self::$currentId = null;
    }

    public static function generate(): string
    {
        return (string) Str::uuid();
    }

    private static function sanitize(string $value): string
    {
        $value = substr($value, 0, self::MAX_LENGTH);

        if (!preg_match(self::PATTERN, $value)) {
            return self::generate();
        }

        return $value;
    }
}

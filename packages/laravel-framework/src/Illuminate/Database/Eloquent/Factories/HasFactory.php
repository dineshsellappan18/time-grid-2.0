<?php

namespace Illuminate\Database\Eloquent\Factories;

trait HasFactory
{
    public static function factory($count = null, ?array $state = null): Factory
    {
        $factoryClass = static::resolveFactoryName();

        $factory = $factoryClass::new($state ?? []);

        if ($count !== null) {
            $factory = $factory->count($count);
        }

        return $factory;
    }

    protected static function resolveFactoryName(): string
    {
        $modelName = class_basename(static::class);

        return "Database\\Factories\\{$modelName}Factory";
    }
}

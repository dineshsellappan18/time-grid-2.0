<?php

namespace App\Support\Php83;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class Reflection
{
    /**
     * PHP 8.3-safe replacement for ReflectionParameter::getClass().
     *
     * @param ReflectionParameter $parameter
     * @return ReflectionClass|null
     */
    public static function classOf(ReflectionParameter $parameter)
    {
        if (method_exists($parameter, 'getType')) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                try {
                    return new ReflectionClass($type->getName());
                } catch (ReflectionException $e) {
                    return null;
                }
            }

            return null;
        }

        return method_exists($parameter, 'getClass') ? $parameter->getClass() : null;
    }
}

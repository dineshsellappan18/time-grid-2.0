<?php

namespace McCool\LaravelAutoPresenter;

/**
 * Minimal BasePresenter stub after removing mccool/laravel-auto-presenter (WO-009).
 */
abstract class BasePresenter
{
    /**
     * @var mixed
     */
    protected $wrappedObject;

    /**
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return $this->wrappedObject->{$property};
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->wrappedObject, $method], $arguments);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->wrappedObject;
    }
}

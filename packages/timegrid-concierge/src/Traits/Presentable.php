<?php

namespace Timegridio\Concierge\Traits;

/**
 * WO-015 / WO-009 residual: Laravel AutoPresenter decoration was removed.
 * Views still call presenter methods on Eloquent models (e.g. $business->industryIcon()).
 * Forward unknown instance method calls / non-attribute property reads to the presenter.
 */
trait Presentable
{
    /**
     * @return object
     */
    public function present()
    {
        $class = $this->getPresenterClass();

        return new $class($this);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->isEloquentProperty($key)) {
            return parent::__get($key);
        }

        $presenter = $this->present();
        if (method_exists($presenter, $key)) {
            return $presenter->{$key}();
        }

        return parent::__get($key);
    }

    /**
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $presenter = $this->present();

        if (method_exists($presenter, $method)) {
            return $presenter->{$method}(...array_values($parameters));
        }

        return parent::__call($method, $parameters);
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function isEloquentProperty($key)
    {
        if (array_key_exists($key, $this->attributes)
            || array_key_exists($key, $this->relations)
            || $this->hasGetMutator($key)
            || method_exists($this, $key)
            || $this->relationLoaded($key)) {
            return true;
        }

        // Unset columns on new models (e.g. status before doReserve) must not
        // route through the presenter — that recurses via statusLabel.
        if (in_array($key, $this->getFillable(), true)
            || in_array($key, $this->getGuarded(), true)
            || in_array($key, $this->getDates(), true)
            || $key === $this->getKeyName()) {
            return true;
        }

        return false;
    }
}

<?php

namespace McCool\LaravelAutoPresenter;

/**
 * Minimal stub retained after removing mccool/laravel-auto-presenter (WO-009).
 * Concierge models still declare this interface; automatic presenter decoration is gone.
 */
interface HasPresenter
{
    /**
     * @return string
     */
    public function getPresenterClass();
}

<?php

namespace Tests\Browser\Pages;

/**
 * WO-006 page object scaffold — vacancy authoring (simple + advanced DSL).
 */
class VacancyEditorPage
{
    public function url()
    {
        return '/manager/business/{business}/vacancy';
    }

    public function elements()
    {
        return [
            '@simple-mode' => '[data-vacancy-mode=simple]',
            '@advanced-mode' => '[data-vacancy-mode=advanced]',
            '@submit' => 'button[type=submit]',
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class LanguageController extends Controller
{
    public function switchLang(string $posixLocale): RedirectResponse
    {
        logger()->info(sprintf('%s: %s', __METHOD__, $posixLocale));

        if (isAcceptedLocale($posixLocale)) {
            $this->setSessionLanguage($posixLocale);
        }

        return redirect()->back();
    }

    protected function setSessionLanguage(string $posixLocale): void
    {
        $localeSubtags = locale_parse($posixLocale);
        $language = Arr::get($localeSubtags, 'language');

        session()->set('language', $language);
        session()->set('applocale', $posixLocale);

        logger()->info("Language Switched: LANG='{$language}' POSIX='{$posixLocale}'");
    }
}

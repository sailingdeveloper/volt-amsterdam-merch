<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    protected const ALLOWED_LOCALE = ['en', 'nl'];

    /**
     * Switch the application language.
     */
    public function switch(string $locale): RedirectResponse
    {
        if (in_array($locale, self::ALLOWED_LOCALE) === false) {
            $locale = 'en';
        }

        Session::put('locale', $locale);

        return back();
    }
}

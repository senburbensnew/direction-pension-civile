<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        if (!in_array($locale, ['en', 'fr'])) {
            abort(400);
        }

        // Store in session
        Session::put('locale', $locale);

        // Optional: Store in cookie for persistent preference
        $cookie = cookie()->forever('preferred_locale', $locale);

        return redirect()->back()->withCookie($cookie);
    }
}
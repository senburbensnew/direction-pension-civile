<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($locale)
    {

        // Store in session
        Session::put('locale', $locale);

        // Store in cookie for persistent preference (matches ExceptionHandler)
        $cookie = cookie()->forever('preferred_locale', $locale); // Changed cookie name to 'locale'

        return redirect()->back()
            ->withCookie($cookie);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        $supportedLocales = config('app.supported_locales', ['en']);
        
        if (!in_array($locale, $supportedLocales)) {
            abort(400, 'Unsupported locale');
        }

        // Store in session
        Session::put('locale', $locale);

        // Store in cookie for persistent preference (matches ExceptionHandler)
        $cookie = cookie()->forever('locale', $locale);

        return redirect()->back()->withCookie($cookie);
    }
}
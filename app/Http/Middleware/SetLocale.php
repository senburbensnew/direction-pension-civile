<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    // app/Http/Middleware/SetLocale.php
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale', $request->cookie('locale', config('app.locale')));

        // Validate against supported locales
        if (!in_array($locale, ['en', 'fr'])) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        // Sync session if not set
        if (!Session::has('locale')) {
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('preferred_locale', 
                  Session::get('locale', 
                  config('app.locale')));

        App::setLocale($locale);
        
        return $next($request);
    }
}
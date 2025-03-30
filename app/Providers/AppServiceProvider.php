<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
/*         Validator::extend('base64_image', function ($attribute, $value, $parameters, $validator) {
            if (!Str::startsWith($value, 'data:image')) return false;
            
            $parts = explode(',', $value);
            if (count($parts) !== 2) return false;
            
            $mime = str_replace(
                ['image/', ';base64'],
                '',
                Str::before($parts[0], ';')
            );
            
            return in_array(strtolower($mime), ['jpg', 'jpeg', 'png', 'gif']);
        }); */
    }
}
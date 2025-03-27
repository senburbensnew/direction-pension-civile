<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Add the correct import for Gate

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
        Gate::define('viewPensionnaireSection', function ($user) {
            return $user->hasRole('pensionnaire') || $user->hasRole('admin');
        });
    
        Gate::define('viewFonctionnaireSection', function ($user) {
            return $user->hasRole('fonctionnaire') || $user->hasRole('admin');
        });
    
        Gate::define('viewInstitutionSection', function ($user) {
            return $user->hasRole('institution') || $user->hasRole('admin');
        });
    }
}
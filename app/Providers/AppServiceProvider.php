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
        // Define the gates for the roles
        Gate::define('viewPensionnaireSection', function ($user) {
            return $user->hasRole('pensionnaire');
        });
    
        Gate::define('viewFonctionnaireSection', function ($user) {
            return $user->hasRole('fonctionnaire');
        });
    
        Gate::define('viewInstitutionSection', function ($user) {
            return $user->hasRole('institution');
        });
    }
}
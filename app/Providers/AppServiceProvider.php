<?php

namespace App\Providers;

use App\Models\Demande;
use App\Observers\DemandeObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

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
        Demande::observe(DemandeObserver::class);

        Gate::after(function ($user, $ability){
            if($user->hasRole('admin')){
                return true;
            }
        });
    }
}
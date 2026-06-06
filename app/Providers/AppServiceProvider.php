<?php

namespace App\Providers;

use App\Models\Demande;
use App\Models\FluxTransition;
use App\Observers\DemandeObserver;
use App\Observers\FluxTransitionObserver;
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
        FluxTransition::observe(FluxTransitionObserver::class);

        Gate::after(function ($user, $ability){
            if($user->hasRole('admin')){
                return true;
            }
        });
    }
}
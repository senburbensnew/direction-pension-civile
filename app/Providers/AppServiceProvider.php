<?php

namespace App\Providers;

use App\Models\Demande;
use App\Observers\DemandeObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Demande::observe(DemandeObserver::class);

        Gate::after(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}

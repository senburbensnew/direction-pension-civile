<?php

namespace App\Providers;

use App\Enums\TypeDemandeEnum;
use App\Models\Beneficiaire;
use App\Models\Demande;
use App\Models\Report;
use App\Models\User;
use App\Policies\BeneficiairePolicy;
use App\Policies\DemandePolicy;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Beneficiaire::class => BeneficiairePolicy::class,
        Demande::class => DemandePolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('createDemandeType', function (User $user, TypeDemandeEnum|string $type) {
            return app(BeneficiairePolicy::class)->createDemandeType($user, $type);
        });
    }
}

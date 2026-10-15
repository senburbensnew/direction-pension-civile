<?php

namespace App\Services\Pension;

use App\Models\BeneficiaireRelationDemande;
use App\Models\DemandeCreationCompte;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Notifications\BeneficiaireRelationApprouveeNotification;
use App\Notifications\BeneficiaireRelationRefuseeNotification;
use App\Notifications\BeneficiaireRelationRevokedNotification;
use App\Notifications\BeneficiaireRelationSoumiseAgentNotification;
use App\Notifications\BeneficiaireRelationSoumiseNotification;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BeneficiaireRelationNotifier
{
    public function notifyRegisteredViaDemandeCompte(
        BeneficiaireRelationDemande $demande,
        DemandeCreationCompte $demandeCompte,
    ): void {
        $demande->loadMissing(['user', 'beneficiaire']);

        foreach ($this->formalitesReviewers() as $agent) {
            $agent->notify(new BeneficiaireRelationSoumiseAgentNotification($demande));
        }
    }

    public function notifySubmitted(BeneficiaireRelationDemande $demande): void
    {
        $demande->loadMissing(['user', 'beneficiaire']);

        $demande->user?->notify(new BeneficiaireRelationSoumiseNotification($demande));

        foreach ($this->formalitesReviewers() as $agent) {
            $agent->notify(new BeneficiaireRelationSoumiseAgentNotification($demande));
        }
    }

    public function notifyApproved(BeneficiaireRelationDemande $demande): void
    {
        $demande->loadMissing(['user', 'beneficiaire']);
        $demande->user?->notify(new BeneficiaireRelationApprouveeNotification($demande));
    }

    public function notifyRefused(BeneficiaireRelationDemande $demande): void
    {
        $demande->loadMissing(['user', 'beneficiaire']);
        $demande->user?->notify(new BeneficiaireRelationRefuseeNotification($demande));
    }

    public function notifyRevoked(UserBeneficiaireLink $link): void
    {
        $link->loadMissing(['user', 'beneficiaire']);
        $link->user?->notify(new BeneficiaireRelationRevokedNotification($link));
    }

    /**
     * @return Collection<int, User>
     */
    private function formalitesReviewers(): Collection
    {
        $users = collect();

        foreach (['admin', User::ROLE_AGENT_FORMALITES] as $roleName) {
            if (Role::query()->where('name', $roleName)->where('guard_name', 'web')->exists()) {
                $users = $users->merge(User::role($roleName)->get());
            }
        }

        if (Permission::query()->where('name', 'beneficiaire_relations.view')->where('guard_name', 'web')->exists()) {
            $users = $users->merge(User::permission('beneficiaire_relations.view')->get());
        }

        return $users
            ->filter(fn (User $user) => $user->canAccessFullServices())
            ->unique('id')
            ->values();
    }
}

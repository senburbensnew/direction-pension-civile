<?php

namespace App\Services\Pension;

use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Models\User;
use App\Notifications\PensionMandatSoumisAgentNotification;
use App\Notifications\PensionMandatSoumisNotification;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class PensionMandatNotifier
{
    public function notifyRegisteredViaDemandeCompte(
        PensionMandat $mandat,
        DemandeCreationCompte $demandeCompte,
    ): void {
        $mandat->loadMissing(['mandataire', 'beneficiaire']);

        $mandat->mandataire?->notify(new PensionMandatSoumisNotification($mandat));

        foreach ($this->mandatReviewers() as $agent) {
            $agent->notify(new PensionMandatSoumisAgentNotification($mandat));
        }
    }

    public function notifySubmitted(PensionMandat $mandat): void
    {
        $mandat->loadMissing(['mandataire', 'beneficiaire']);

        $mandat->mandataire?->notify(new PensionMandatSoumisNotification($mandat));

        foreach ($this->mandatReviewers() as $agent) {
            $agent->notify(new PensionMandatSoumisAgentNotification($mandat));
        }
    }

    public function notifyReadyAfterAccountAccepted(PensionMandat $mandat): void
    {
        $mandat->loadMissing(['mandataire', 'beneficiaire', 'demandeCreationCompte']);

        foreach ($this->mandatReviewers() as $agent) {
            $agent->notify(new PensionMandatSoumisAgentNotification($mandat));
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function mandatReviewers(): Collection
    {
        if (! Role::query()->where('name', User::ROLE_AGENT_FORMALITES)->where('guard_name', 'web')->exists()) {
            return collect();
        }

        return User::role(User::ROLE_AGENT_FORMALITES)
            ->get()
            ->filter(fn (User $user) => $user->canAccessFullServices())
            ->filter(fn (User $user) => $user->can('pension_mandats.view'))
            ->unique('id')
            ->values();
    }
}

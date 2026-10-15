<?php

namespace App\Services;

use App\Enums\RencontreStatutEnum;
use App\Models\Demande;
use App\Models\User;

class RencontreStatutAlignmentService
{
    public function __construct(
        private RencontreWorkflowService $workflow,
    ) {}

    /**
     * Passe en « réalisé » un RDV encore actif lorsque la formalité est déjà enregistrée.
     */
    public function alignAfterFormalite(Demande $demande, User $user): Demande
    {
        if (! $demande->isRencontre()) {
            return $demande;
        }

        if (
            $this->workflow->statut($demande)->isTerminal()
        ) {
            return $demande;
        }

        if (
            $this->workflow->statut($demande) === RencontreStatutEnum::ACTIF
            && $demande->formaliteRdvEnregistree()
            && $demande->rencontreHeureEstPassee()
        ) {
            return $this->workflow->clore(
                $demande,
                $user,
                RencontreStatutEnum::REALISE,
                'Statut aligné : formalité déjà enregistrée pour ce rendez-vous.'
            );
        }

        return $demande;
    }
}

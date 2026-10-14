<?php

namespace App\Policies;

use App\Models\AffiliationPension;
use App\Models\Demande;
use App\Models\User;
use App\Services\Pension\BeneficiaireAccessService;
use Illuminate\Auth\Access\HandlesAuthorization;

class DemandePolicy
{
    use HandlesAuthorization;

    public function access(User $user, Demande $demande)
    {
        return $user->service_id === $demande->current_service_id;
    }

    public function view(User $user, Demande $demande)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($demande->user_id === $user->id) {
            return true;
        }

        if ((int) $demande->created_by === (int) $user->id) {
            return true;
        }

        $type = $demande->type?->value ?? (string) $demande->type;
        $code = filled($demande->data['code_pension'] ?? null)
            ? AffiliationPension::normalizeCode((string) $demande->data['code_pension'])
            : null;

        return app(BeneficiaireAccessService::class)->userCanViewDemande(
            $user,
            $type,
            $demande->beneficiaire_id,
            $code,
        );
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->canAccessFullServices();
    }

    public function createType(User $user, string $typeDemande): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $beneficiaire = $user->pensionContext()->resolveBeneficiaire();

        if (! $beneficiaire) {
            return $user->canAccessFullServices();
        }

        return $user->can('createDemande', [$beneficiaire, $typeDemande]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Models\BeneficiaireRelationDemande;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Services\Pension\BeneficiaireAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BeneficiaireEspaceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $access = app(BeneficiaireAccessService::class);

        $links = $access->grantingLinksFor($user)->load('beneficiaire.affiliations');

        $mandats = PensionMandat::query()
            ->where('user_id', $user->id)
            ->with(['beneficiaire.affiliations', 'demandeCreationCompte'])
            ->latest()
            ->get();

        $relationDemandes = BeneficiaireRelationDemande::query()
            ->where('user_id', $user->id)
            ->with(['beneficiaire.affiliations', 'demandeCreationCompte'])
            ->latest()
            ->limit(20)
            ->get();

        $demandesCompteRepresentant = DemandeCreationCompte::query()
            ->where('user_id', $user->id)
            ->where('is_mineur', true)
            ->latest()
            ->limit(5)
            ->get();

        $pendingRelations = $relationDemandes->filter(
            fn ($demande) => $demande->status === BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE
        )->values();

        $relationChoices = array_filter(
            BeneficiaireRelationEnum::cases(),
            fn (BeneficiaireRelationEnum $c) => ! in_array($c, [
                BeneficiaireRelationEnum::SELF,
                BeneficiaireRelationEnum::MANDATAIRE,
            ], true)
        );

        return view('compte.beneficiaires.index', compact(
            'links',
            'mandats',
            'pendingRelations',
            'relationDemandes',
            'demandesCompteRepresentant',
            'relationChoices',
        ));
    }
}

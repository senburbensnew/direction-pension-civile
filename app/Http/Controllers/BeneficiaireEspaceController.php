<?php

namespace App\Http\Controllers;

use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Models\BeneficiaireRelationDemande;
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
            ->with('beneficiaire.affiliations')
            ->latest()
            ->get();

        $pendingRelations = BeneficiaireRelationDemande::query()
            ->where('user_id', $user->id)
            ->where('status', BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE->value)
            ->with('beneficiaire')
            ->latest()
            ->get();

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
            'relationChoices',
        ));
    }
}

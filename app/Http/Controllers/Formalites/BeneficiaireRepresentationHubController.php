<?php

namespace App\Http\Controllers\Formalites;

use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\TypeDemandeEnum;
use App\Http\Controllers\Controller;
use App\Models\BeneficiaireRelationDemande;
use App\Models\PensionMandat;
use App\Models\UserBeneficiaireLink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BeneficiaireRepresentationHubController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString() ?: 'mandats';

        $mandats = PensionMandat::query()
            ->with(['mandataire', 'beneficiaire.affiliations'])
            ->latest()
            ->paginate(20, pageName: 'mandats_page');

        $demandeTypeChoices = TypeDemandeEnum::mandatScopeChoices();

        $relationDemandes = BeneficiaireRelationDemande::query()
            ->with(['user', 'beneficiaire.affiliations'])
            ->latest()
            ->paginate(20, pageName: 'relations_page');

        $activeRepresentativeLinks = UserBeneficiaireLink::query()
            ->where('status', BeneficiaireLinkStatusEnum::ACTIVE->value)
            ->where('relation', '!=', BeneficiaireRelationEnum::MANDATAIRE->value)
            ->with(['user', 'beneficiaire.affiliations'])
            ->latest('verifie_le')
            ->limit(50)
            ->get();

        return view('formalites.representations.index', compact(
            'tab',
            'mandats',
            'demandeTypeChoices',
            'relationDemandes',
            'activeRepresentativeLinks',
        ));
    }
}

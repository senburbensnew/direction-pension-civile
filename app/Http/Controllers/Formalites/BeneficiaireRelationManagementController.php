<?php

namespace App\Http\Controllers\Formalites;

use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Formalites\ApproveBeneficiaireRelationDemandeRequest;
use App\Http\Requests\Formalites\RefuseBeneficiaireRelationDemandeRequest;
use App\Models\BeneficiaireRelationDemande;
use App\Models\UserBeneficiaireLink;
use App\Services\Pension\BeneficiaireRelationService;
use Illuminate\Http\RedirectResponse;

class BeneficiaireRelationManagementController extends Controller
{
    public function index()
    {
        $demandes = BeneficiaireRelationDemande::query()
            ->with(['user', 'beneficiaire.affiliations', 'demandeCreationCompte'])
            ->latest()
            ->paginate(20);

        $activeLinks = UserBeneficiaireLink::query()
            ->where('status', BeneficiaireLinkStatusEnum::ACTIVE->value)
            ->where('relation', '!=', BeneficiaireRelationEnum::MANDATAIRE->value)
            ->with(['user', 'beneficiaire.affiliations'])
            ->latest('verifie_le')
            ->limit(50)
            ->get();

        return view('formalites.beneficiaire-relations.index', compact('demandes', 'activeLinks'));
    }

    public function approve(ApproveBeneficiaireRelationDemandeRequest $request, BeneficiaireRelationDemande $demande): RedirectResponse
    {
        $validated = $request->validated();

        $permissions = filled($validated['permissions'] ?? null)
            ? array_values($validated['permissions'])
            : null;

        app(BeneficiaireRelationService::class)->approve($demande, $request->user(), $permissions);

        return back()->with('success', 'Relation approuvée.');
    }

    public function refuse(RefuseBeneficiaireRelationDemandeRequest $request, BeneficiaireRelationDemande $demande): RedirectResponse
    {
        $validated = $request->validated();

        app(BeneficiaireRelationService::class)->refuse(
            $demande,
            $request->user(),
            $validated['refusal_reason'],
        );

        return back()->with('success', 'Demande refusée.');
    }

    public function revokeLink(UserBeneficiaireLink $link): RedirectResponse
    {
        app(BeneficiaireRelationService::class)->revokeLink(
            $link,
            request()->user(),
            'Révoquée par Formalités',
        );

        return back()->with('success', 'Lien révoqué.');
    }
}

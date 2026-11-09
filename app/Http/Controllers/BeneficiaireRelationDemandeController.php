<?php

namespace App\Http\Controllers;

use App\Enums\BeneficiaireRelationEnum;
use App\Http\Requests\StoreBeneficiaireRelationDemandeRequest;
use App\Services\Pension\BeneficiaireRelationService;
use Illuminate\Http\RedirectResponse;

class BeneficiaireRelationDemandeController extends Controller
{
    public function create()
    {
        $relationChoices = array_filter(
            BeneficiaireRelationEnum::cases(),
            fn (BeneficiaireRelationEnum $c) => ! in_array($c, [
                BeneficiaireRelationEnum::SELF,
                BeneficiaireRelationEnum::MANDATAIRE,
            ], true)
        );

        return view('compte.beneficiaires.create-relation', compact('relationChoices'));
    }

    public function store(StoreBeneficiaireRelationDemandeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $type = BeneficiaireRelationEnum::from($validated['type_relation']);

        app(BeneficiaireRelationService::class)->submitDemande(
            $request->user(),
            $validated['code_pension'],
            $type,
            $validated['motif'] ?? null,
            $request->file('justificatif'),
        );

        return redirect()
            ->route('compte.beneficiaires.index')
            ->with('success', 'Demande de relation soumise. Un agent la validera.');
    }
}

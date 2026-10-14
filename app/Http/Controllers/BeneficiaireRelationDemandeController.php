<?php

namespace App\Http\Controllers;

use App\Enums\BeneficiaireRelationEnum;
use App\Rules\CodePension;
use App\Services\Pension\BeneficiaireRelationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code_pension' => ['required', 'string', new CodePension],
            'type_relation' => ['required', 'string', 'in:pere,mere,tuteur'],
            'motif' => ['nullable', 'string', 'max:2000'],
            'justificatif' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

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

<?php

namespace App\Http\Controllers;

use App\Enums\PensionMandatStatusEnum;
use App\Enums\PensionMandatTypeEnum;
use App\Models\Beneficiaire;
use App\Models\PensionMandat;
use App\Rules\CodePension;
use App\Services\Pension\BeneficiaireProvisioningService;
use App\Services\Pension\PensionMandatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PensionMandatController extends Controller
{
    public function index(Request $request)
    {
        $mandats = $request->user()
            ->pensionMandats()
            ->with(['beneficiaire.affiliations', 'demandeCreationCompte'])
            ->latest()
            ->get();

        $demandesCompteMandataire = \App\Models\DemandeCreationCompte::query()
            ->where('user_id', $request->user()->id)
            ->where('is_mandataire', true)
            ->where('status', \App\Models\DemandeCreationCompte::STATUS_EN_ATTENTE)
            ->latest()
            ->limit(5)
            ->get();

        $mandatTypeChoices = PensionMandatTypeEnum::selectOptions();

        return view('compte.pension-mandats', compact(
            'mandats',
            'demandesCompteMandataire',
            'mandatTypeChoices',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $typeValues = array_map(
            fn (PensionMandatTypeEnum $case) => $case->value,
            PensionMandatTypeEnum::cases(),
        );

        $validated = $request->validate([
            'beneficiaire_firstname' => ['required', 'string', 'max:120'],
            'beneficiaire_lastname' => ['required', 'string', 'max:120'],
            'beneficiaire_nif' => ['nullable', 'string', 'max:32'],
            'code_pension' => ['required', 'string', new CodePension()],
            'type_mandat' => ['required', 'string', 'in:'.implode(',', $typeValues)],
            'type_autre_precision' => [
                'nullable',
                'string',
                'max:120',
                'required_if:type_mandat,autre',
            ],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'mandat_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $resolvedType = PensionMandatTypeEnum::storedType(
            $validated['type_mandat'],
            $validated['type_autre_precision'] ?? null,
        );

        if (mb_strlen($resolvedType) > 64) {
            return back()
                ->withInput()
                ->withErrors([
                    'type_autre_precision' => 'La précision du type de mandat est trop longue.',
                ]);
        }

        $user = $request->user();

        app(PensionMandatService::class)->assertCanSubmitMandat($user, $validated['code_pension']);

        $mandat = null;

        DB::transaction(function () use ($validated, $user, $request, $resolvedType, &$mandat) {
            $beneficiaire = Beneficiaire::create([
                'firstname' => $validated['beneficiaire_firstname'],
                'lastname' => $validated['beneficiaire_lastname'],
                'nif' => $validated['beneficiaire_nif'] ?? null,
                'is_mineur' => false,
            ]);

            app(BeneficiaireProvisioningService::class)->syncAffiliation(
                $beneficiaire,
                $validated['code_pension'],
                true
            );

            $mandat = PensionMandat::create([
                'user_id' => $user->id,
                'beneficiaire_id' => $beneficiaire->id,
                'type' => $resolvedType,
                'status' => PensionMandatStatusEnum::EN_ATTENTE,
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($request->hasFile('mandat_document')) {
                $mandat->addMediaFromRequest('mandat_document')
                    ->toMediaCollection('mandat_document');
            }
        });

        app(\App\Services\Pension\PensionMandatNotifier::class)->notifySubmitted($mandat->fresh());

        return redirect()
            ->route('compte.mandats.index')
            ->with('success', 'Demande de mandat soumise. Un agent validera votre dossier.');
    }
}

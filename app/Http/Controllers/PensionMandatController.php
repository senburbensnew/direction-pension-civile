<?php

namespace App\Http\Controllers;

use App\Enums\PensionMandatStatusEnum;
use App\Models\Beneficiaire;
use App\Models\PensionMandat;
use App\Rules\CodePension;
use App\Notifications\PensionMandatSoumisNotification;
use App\Services\Pension\BeneficiaireProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PensionMandatController extends Controller
{
    public function index(Request $request)
    {
        $mandats = $request->user()
            ->pensionMandats()
            ->with('beneficiaire')
            ->latest()
            ->get();

        return view('compte.pension-mandats', compact('mandats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'beneficiaire_firstname' => ['required', 'string', 'max:120'],
            'beneficiaire_lastname' => ['required', 'string', 'max:120'],
            'beneficiaire_nif' => ['nullable', 'string', 'max:32'],
            'code_pension' => ['required', 'string', new CodePension()],
            'type' => ['required', 'string', 'max:64'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'mandat_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = $request->user();

        $mandat = null;

        DB::transaction(function () use ($validated, $user, $request, &$mandat) {
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
                'type' => $validated['type'],
                'status' => PensionMandatStatusEnum::EN_ATTENTE,
                'date_debut' => $validated['date_debut'] ?? null,
                'date_fin' => $validated['date_fin'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($request->hasFile('mandat_document')) {
                $mandat->addMediaFromRequest('mandat_document')
                    ->toMediaCollection('mandat_document');
            }
        });

        $user->notify(new PensionMandatSoumisNotification($mandat->fresh()));

        return redirect()
            ->route('compte.mandats.index')
            ->with('success', 'Demande de mandat soumise. Un agent validera votre dossier.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PensionMandatStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\PensionMandat;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\TypeDemandeEnum;
use App\Notifications\PensionMandatApprouveNotification;
use App\Notifications\PensionMandatRefuseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PensionMandatManagementController extends Controller
{
    public function index()
    {
        $mandats = PensionMandat::query()
            ->with(['mandataire', 'beneficiaire.affiliations'])
            ->latest()
            ->paginate(20);

        $demandeTypeChoices = TypeDemandeEnum::mandatScopeChoices();

        return view('admin.pension-mandats.index', compact('mandats', 'demandeTypeChoices'));
    }

    public function approve(Request $request, PensionMandat $mandat): RedirectResponse
    {
        $validated = $request->validate([
            'allowed_demande_types' => ['nullable', 'array'],
            'allowed_demande_types.*' => ['string', 'max:64'],
            'allowed_pension_codes' => ['nullable', 'array'],
            'allowed_pension_codes.*' => ['string', 'max:32'],
        ]);

        /** @var User $reviewer */
        $reviewer = $request->user();

        DB::transaction(function () use ($mandat, $reviewer, $validated) {
            $mandat->update([
                'status' => PensionMandatStatusEnum::ACTIF,
                'validated_by' => $reviewer->id,
                'validated_at' => now(),
                'refusal_reason' => null,
                'allowed_demande_types' => filled($validated['allowed_demande_types'] ?? null)
                    ? array_values($validated['allowed_demande_types'])
                    : null,
                'allowed_pension_codes' => filled($validated['allowed_pension_codes'] ?? null)
                    ? array_values($validated['allowed_pension_codes'])
                    : null,
            ]);

            UserBeneficiaireLink::query()->firstOrCreate(
                [
                    'user_id' => $mandat->user_id,
                    'beneficiaire_id' => $mandat->beneficiaire_id,
                    'relation' => BeneficiaireRelationEnum::MANDATAIRE->value,
                ],
                []
            );
        });

        $mandat = $mandat->fresh();
        $mandat->mandataire?->notify(new PensionMandatApprouveNotification($mandat));

        return back()->with('success', 'Mandat approuvé.');
    }

    public function refuse(Request $request, PensionMandat $mandat): RedirectResponse
    {
        $validated = $request->validate([
            'refusal_reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $mandat->update([
            'status' => PensionMandatStatusEnum::REFUSE,
            'refusal_reason' => $validated['refusal_reason'],
            'validated_at' => now(),
            'validated_by' => $request->user()->id,
        ]);

        $mandat->mandataire?->notify(new PensionMandatRefuseNotification($mandat->fresh(), $validated['refusal_reason']));

        return back()->with('success', 'Mandat refusé.');
    }

    public function revoke(PensionMandat $mandat): RedirectResponse
    {
        $mandat->update([
            'status' => PensionMandatStatusEnum::REVOQUE,
        ]);

        return back()->with('success', 'Mandat révoqué.');
    }
}

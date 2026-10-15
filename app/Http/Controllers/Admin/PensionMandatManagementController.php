<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Http\Controllers\Controller;
use App\Models\BeneficiaireRelationEvent;
use App\Models\PensionMandat;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Notifications\PensionMandatApprouveNotification;
use App\Services\Pension\BeneficiaireAccessService;
use App\Services\Pension\PensionMandatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PensionMandatManagementController extends Controller
{
    public function index()
    {
        $mandats = PensionMandat::query()
            ->with(['mandataire', 'beneficiaire.affiliations', 'demandeCreationCompte'])
            ->latest()
            ->paginate(20);

        $demandeTypeChoices = TypeDemandeEnum::mandatScopeChoices();

        return view('admin.pension-mandats.index', compact('mandats', 'demandeTypeChoices'));
    }

    public function approve(Request $request, PensionMandat $mandat): RedirectResponse
    {
        if (! $mandat->beneficiaire_id && $mandat->demandeCreationCompte) {
            app(PensionMandatService::class)->syncBeneficiaireFromDemandeCreationCompte(
                $mandat->demandeCreationCompte->fresh()
            );
            $mandat->refresh();
        }

        if (! $mandat->beneficiaire_id) {
            return back()->withErrors([
                'beneficiaire' => 'Le pensionné mandant doit être identifié (code pension) avant d’approuver le mandat.',
            ]);
        }

        $validated = $request->validate([
            'allowed_demande_types' => ['nullable', 'array'],
            'allowed_demande_types.*' => ['string', 'max:64'],
            'allowed_pension_codes' => ['nullable', 'array'],
            'allowed_pension_codes.*' => ['string', 'max:32'],
        ]);

        /** @var User $reviewer */
        $reviewer = $request->user();

        DB::transaction(function () use ($mandat, $reviewer, $validated) {
            $before = $mandat->only(['status', 'allowed_demande_types', 'allowed_pension_codes']);

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

            $link = UserBeneficiaireLink::query()->updateOrCreate(
                [
                    'user_id' => $mandat->user_id,
                    'beneficiaire_id' => $mandat->beneficiaire_id,
                    'relation' => BeneficiaireRelationEnum::MANDATAIRE->value,
                ],
                [
                    'status' => BeneficiaireLinkStatusEnum::ACTIVE,
                    'verifie_par' => $reviewer->id,
                    'verifie_le' => now(),
                ]
            );

            BeneficiaireRelationEvent::record(
                $mandat,
                'mandat_approuve',
                $reviewer,
                $before,
                $mandat->fresh()->only(['status', 'allowed_demande_types', 'allowed_pension_codes']),
            );

            BeneficiaireRelationEvent::record(
                $link,
                'relation_approuvee',
                $reviewer,
                null,
                $link->toArray(),
                'Via mandat #'.$mandat->id,
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

        /** @var User $reviewer */
        $reviewer = $request->user();

        app(PensionMandatService::class)->refuse($mandat, $reviewer, $validated['refusal_reason']);

        return back()->with('success', 'Mandat refusé.');
    }

    public function revoke(Request $request, PensionMandat $mandat): RedirectResponse
    {
        /** @var User $reviewer */
        $reviewer = $request->user();

        DB::transaction(function () use ($mandat, $reviewer) {
            $before = $mandat->only(['status']);
            $mandat->update([
                'status' => PensionMandatStatusEnum::REVOQUE,
            ]);

            BeneficiaireRelationEvent::record(
                $mandat,
                'mandat_revoque',
                $reviewer,
                $before,
                $mandat->only(['status']),
            );

            app(BeneficiaireAccessService::class)->revokeMandataireLink(
                $mandat->user_id,
                $mandat->beneficiaire_id,
                $reviewer,
            );
        });

        return back()->with('success', 'Mandat révoqué.');
    }
}

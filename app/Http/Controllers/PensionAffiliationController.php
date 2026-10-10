<?php

namespace App\Http\Controllers;

use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Rules\CodePension;
use App\Rules\PensionnaireReferenceExists;
use App\Services\Pension\ActivePensionContext;
use App\Services\Pension\BeneficiaireProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PensionAffiliationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $context = ActivePensionContext::for($user);

        $beneficiaires = $context->accessibleBeneficiaires()->load('affiliations');

        return view('compte.pension-affiliations', [
            'beneficiaires' => $beneficiaires,
            'activeBeneficiaire' => $context->resolveBeneficiaire(),
            'activeAffiliation' => $context->resolveAffiliation(),
        ]);
    }

    public function store(Request $request, BeneficiaireProvisioningService $provisioning): RedirectResponse
    {
        $validated = $request->validate([
            'beneficiaire_id' => ['required', 'integer', 'exists:beneficiaires,id'],
            'code_pension' => [
                'required',
                'string',
                new CodePension(),
                new PensionnaireReferenceExists(),
            ],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $context = ActivePensionContext::for($user);
        $beneficiaire = Beneficiaire::query()->findOrFail($validated['beneficiaire_id']);

        if (! $context->userCanAccessBeneficiaire($beneficiaire)) {
            abort(403);
        }

        $code = AffiliationPension::normalizeCode($validated['code_pension']);

        if (AffiliationPension::codeExists($code)) {
            throw ValidationException::withMessages([
                'code_pension' => 'Ce code pension est déjà rattaché à un dossier.',
            ]);
        }

        $affiliation = $provisioning->syncAffiliation(
            $beneficiaire,
            $code,
            $request->boolean('is_primary', false)
        );

        if ($request->boolean('is_primary') && $affiliation) {
            session([
                ActivePensionContext::SESSION_AFFILIATION => $affiliation->id,
            ]);
        }

        return back()->with('success', 'Code pension ajouté.');
    }

    public function destroy(Request $request, AffiliationPension $affiliation): RedirectResponse
    {
        $user = $request->user();
        $context = ActivePensionContext::for($user);

        if (! $context->userCanAccessBeneficiaire($affiliation->beneficiaire)) {
            abort(403);
        }

        if ($affiliation->beneficiaire->affiliations()->count() <= 1) {
            throw ValidationException::withMessages([
                'affiliation' => 'Vous devez conserver au moins un code pension.',
            ]);
        }

        $wasPrimary = $affiliation->is_primary;
        $removedCode = $affiliation->code_pension;
        $beneficiaireId = $affiliation->beneficiaire_id;
        $affiliation->delete();

        if ($wasPrimary) {
            $next = AffiliationPension::query()
                ->where('beneficiaire_id', $beneficiaireId)
                ->first();

            if ($next) {
                $next->update(['is_primary' => true]);
                session([
                    \App\Services\Pension\ActivePensionContext::SESSION_AFFILIATION => $next->id,
                ]);
            }
        }

        return back()->with('success', 'Code pension retiré.');
    }
}

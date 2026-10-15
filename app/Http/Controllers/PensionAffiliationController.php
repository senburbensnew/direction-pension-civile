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
            'loc_description' => $this->descriptionRules(),
        ]);

        $validated['loc_description'] = trim($validated['loc_description']);

        $user = $request->user();
        $context = ActivePensionContext::for($user);
        $beneficiaire = Beneficiaire::query()->findOrFail($validated['beneficiaire_id']);

        if (! $context->userCanAccessBeneficiaire($beneficiaire)) {
            abort(403);
        }

        $code = AffiliationPension::normalizeCode($validated['code_pension']);
        $existing = AffiliationPension::findByCode($code);

        if ($existing) {
            if ($existing->beneficiaire_id !== $beneficiaire->id) {
                throw ValidationException::withMessages([
                    'code_pension' => 'Ce code pension est déjà rattaché à un dossier.',
                ]);
            }

            if ($request->boolean('is_primary')) {
                $affiliation = $this->markAffiliationPrimary($provisioning, $beneficiaire, $code);
                $this->applyDescription($affiliation, $validated['loc_description']);

                return back()->with('success', 'Code pension défini comme principal.');
            }

            $this->applyDescription($existing, $validated['loc_description']);

            return back()->with('info', 'Ce code est déjà rattaché à ce dossier.');
        }

        $affiliation = $provisioning->syncAffiliation(
            $beneficiaire,
            $code,
            $request->boolean('is_primary', false)
        );

        if ($affiliation) {
            $this->applyDescription($affiliation, $validated['loc_description']);
        }

        if ($request->boolean('is_primary') && $affiliation) {
            $this->rememberAffiliationInSession($beneficiaire, $affiliation);
        }

        return back()->with('success', 'Code pension ajouté.');
    }

    public function updateDescription(Request $request, AffiliationPension $affiliation): RedirectResponse
    {
        $validated = $request->validate([
            'loc_description' => $this->descriptionRules(),
        ]);

        $user = $request->user();
        $context = ActivePensionContext::for($user);

        if (! $context->userCanAccessBeneficiaire($affiliation->beneficiaire)) {
            abort(403);
        }

        $affiliation->update([
            'loc_description' => trim($validated['loc_description']),
        ]);

        return back()->with('success', 'Description enregistrée.');
    }

    public function setPrimary(
        Request $request,
        AffiliationPension $affiliation,
        BeneficiaireProvisioningService $provisioning,
    ): RedirectResponse {
        $user = $request->user();
        $context = ActivePensionContext::for($user);
        $beneficiaire = $affiliation->beneficiaire;

        if (! $context->userCanAccessBeneficiaire($beneficiaire)) {
            abort(403);
        }

        if ($affiliation->is_primary) {
            return back()->with('info', 'Ce code est déjà principal.');
        }

        $this->markAffiliationPrimary($provisioning, $beneficiaire, $affiliation->code_pension);

        return back()->with('success', 'Code pension principal mis à jour.');
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

        if ($affiliation->is_primary) {
            throw ValidationException::withMessages([
                'affiliation' => 'Le code pension principal ne peut pas être retiré. Définissez d’abord un autre code comme principal.',
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

    private function markAffiliationPrimary(
        BeneficiaireProvisioningService $provisioning,
        Beneficiaire $beneficiaire,
        string $code,
    ): AffiliationPension {
        $affiliation = $provisioning->syncAffiliation($beneficiaire, $code, true);

        if (! $affiliation) {
            abort(404);
        }

        $this->rememberAffiliationInSession($beneficiaire, $affiliation);

        return $affiliation;
    }

    private function rememberAffiliationInSession(Beneficiaire $beneficiaire, AffiliationPension $affiliation): void
    {
        session([
            ActivePensionContext::SESSION_BENEFICIAIRE => $beneficiaire->id,
            ActivePensionContext::SESSION_AFFILIATION => $affiliation->id,
        ]);
    }

    private function applyDescription(AffiliationPension $affiliation, string $description): void
    {
        $affiliation->update([
            'loc_description' => trim($description),
        ]);
    }

    /**
     * @return list<string|\Illuminate\Contracts\Validation\ValidationRule>
     */
    private function descriptionRules(): array
    {
        return ['required', 'string', 'max:255', 'regex:/\S/'];
    }
}

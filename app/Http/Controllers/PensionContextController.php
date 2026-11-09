<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePensionContextRequest;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Services\Pension\ActivePensionContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PensionContextController extends Controller
{
    public function update(UpdatePensionContextRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();
        $context = ActivePensionContext::for($user);

        $beneficiaire = Beneficiaire::query()->findOrFail($validated['beneficiaire_id']);

        if (! $context->userCanAccessBeneficiaire($beneficiaire)) {
            abort(403);
        }

        $affiliationId = $validated['affiliation_id'] ?? null;

        if ($affiliationId) {
            $affiliation = AffiliationPension::query()->findOrFail($affiliationId);

            if ($affiliation->beneficiaire_id !== $beneficiaire->id) {
                abort(403);
            }
        }

        $context->setContext((int) $validated['beneficiaire_id'], $affiliationId ? (int) $affiliationId : null);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'beneficiaire_id' => (int) $validated['beneficiaire_id'],
                'affiliation_id' => $affiliationId ? (int) $affiliationId : null,
                'display_code' => $context->displayPensionCode(),
            ]);
        }

        return back()->with('success', 'Contexte pension mis à jour.');
    }

    public function activate(Beneficiaire $beneficiaire): RedirectResponse
    {
        $user = request()->user();
        $context = ActivePensionContext::for($user);

        if (! $context->userCanAccessBeneficiaire($beneficiaire)) {
            abort(403);
        }

        $context->setContext($beneficiaire->id);

        return redirect()
            ->route('compte.beneficiaires.index')
            ->with('success', 'Contexte pension mis à jour.');
    }
}

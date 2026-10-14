<?php

namespace App\Http\Controllers;

use App\Models\DemandeCreationCompte;
use App\Services\AccountCreation\DemandeCreationCompteOwnerUpdateService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MonDemandeCreationCompteController extends Controller
{
    public function __construct(
        private DemandeCreationCompteOwnerUpdateService $ownerUpdate
    ) {}

    public function show(Request $request)
    {
        $demande = $this->resolveOwnedDemande($request);

        $demande->load(['histories.actor', 'user']);

        return view('compte.demande-creation.show', [
            'demande' => $demande,
            'editable' => $demande->isPending(),
            'pieces' => $demande->identityPieces(),
        ]);
    }

    public function update(Request $request)
    {
        $demande = $this->resolveOwnedDemande($request);

        $validated = $request->validate(
            DemandeCreationCompteOwnerUpdateService::validationRules(),
            [],
            [
                'firstname' => 'prénom',
                'lastname' => 'nom',
                'email' => 'adresse e-mail',
                'telephone' => 'téléphone',
                'adresse' => 'adresse',
                'nif' => 'matricule fiscal (NIF)',
                'ninu' => 'NINU',
                'pension_code' => 'code pension',
            ]
        );

        try {
            $this->ownerUpdate->update($demande, $request->user(), $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if ($request->hasFile('piece_identite') && $request->filled('piece_identite_type')) {
            $this->attachIdentityPiece($request, $demande);
        }

        return redirect()
            ->route('compte.demande.show')
            ->with('success', 'Votre demande a été mise à jour.');
    }

    private function resolveOwnedDemande(Request $request): DemandeCreationCompte
    {
        $demande = $request->user()?->demandeCreationCompte;

        if (! $demande) {
            abort(404, 'Aucune demande de création de compte associée à votre profil.');
        }

        return $demande;
    }

    private function attachIdentityPiece(Request $request, DemandeCreationCompte $demande): void
    {
        $type = $request->string('piece_identite_type')->toString();
        $collections = [
            'permis' => 'identite_permis',
            'passeport' => 'identite_passeport',
            'cin' => 'identite_cin',
        ];

        if (! isset($collections[$type])) {
            return;
        }

        $request->validate([
            'piece_identite' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $demande
            ->addMediaFromRequest('piece_identite')
            ->toMediaCollection($collections[$type]);
    }
}

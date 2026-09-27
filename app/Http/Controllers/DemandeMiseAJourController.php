<?php

namespace App\Http\Controllers;

use App\Enums\TypeDemandeEnum;
use App\Helpers\CodeGeneratorService;
use App\Http\Requests\StoreDemandeMiseAJourRequest;
use App\Models\Demande;
use App\Models\WorkflowStep;
use App\Services\DemandeWorkflowService;
use Illuminate\Support\Facades\DB;

class DemandeMiseAJourController extends Controller
{
    public const SITUATIONS_MATRIMONIALES = [
        'celibataire' => 'Célibataire',
        'marie' => 'Marié(e)',
        'divorce' => 'Divorcé(e)',
        'veuf' => 'Veuf/Veuve',
        'autre' => 'Autre',
    ];

    public const SITUATIONS_PENSION = [
        'reguliere' => 'Pension régulièrement perçue',
        'difficulte' => 'Difficulté de paiement',
        'changement_bancaire' => 'Changement de coordonnées bancaires',
        'autre' => 'Autre',
    ];

    public const CHANGEMENTS = [
        'adresse' => 'Adresse',
        'telephone' => 'Téléphone',
        'courriel' => 'Courriel',
        'etat_civil' => 'État civil',
        'situation_familiale' => 'Situation familiale',
        'coordonnees_bancaires' => 'Coordonnées bancaires',
        'autre' => 'Autre',
    ];

    public function __construct(private DemandeWorkflowService $workflowService)
    {
    }

    public function create()
    {
        $user = auth()->user();

        return view('demandes.mise-a-jour.create', [
            'identite' => [
                'nom' => old('nom', $user?->lastname ?: ''),
                'prenom' => old('prenom', $user?->firstname ?: ''),
                'numero_pension' => old('numero_pension', $user?->pension_code ?: ''),
                'nif' => old('nif', $user?->nif ?: ''),
                'cinu' => old('cinu', $user?->ninu ?: ''),
                'telephone' => old('telephone', $user?->phone ?: ''),
                'email' => old('email', $user?->email ?: ''),
                'declaration_nom' => old('declaration_nom', $user?->displayName() ?: ''),
            ],
            'situationsMatrimoniales' => self::SITUATIONS_MATRIMONIALES,
            'situationsPension' => self::SITUATIONS_PENSION,
            'changements' => self::CHANGEMENTS,
        ]);
    }

    public function store(StoreDemandeMiseAJourRequest $request)
    {
        $validated = $request->validated();
        $files = ['piece_identite', 'acte_etat_civil', 'justificatif_domicile', 'document_bancaire', 'autre_justificatif'];
        foreach ($files as $file) {
            unset($validated[$file]);
        }

        $validated['changements'] = $request->input('changement') === 'oui'
            ? array_values((array) $request->input('changements', []))
            : [];
        $validated['certifie_at'] = now()->toIso8601String();

        $demande = DB::transaction(function () use ($validated, $request) {
            $demande = Demande::create([
                'code' => CodeGeneratorService::generateUniqueRequestCode(
                    TypeDemandeEnum::DEMANDE_MISE_A_JOUR->value,
                    (new Demande())->getTable()
                ),
                'type' => TypeDemandeEnum::DEMANDE_MISE_A_JOUR->value,
                'title' => 'Mise à jour des informations du pensionné',
                'created_by' => $request->user()->id,
                'current_step_id' => WorkflowStep::idForCode('BROUILLON'),
                'data' => $validated,
            ]);

            foreach (['piece_identite', 'acte_etat_civil', 'justificatif_domicile', 'document_bancaire', 'autre_justificatif'] as $collection) {
                if ($request->hasFile($collection)) {
                    $demande->addMediaFromRequest($collection)->toMediaCollection($collection);
                }
            }

            $this->workflowService->submit($demande, $request->user());

            return $demande->fresh();
        });

        return redirect()
            ->route('demandes.mise-a-jour.create')
            ->with('success', "Votre questionnaire a été transmis. Référence : {$demande->code}");
    }
}

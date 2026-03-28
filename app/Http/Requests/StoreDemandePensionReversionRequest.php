<?php

namespace App\Http\Requests;

use App\Rules\CodePension;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandePensionReversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        logger()->info('Request size', [
            'total_mb' => round(strlen($this->getContent()) / 1024 / 1024, 2),
            'files_mb' => round(
                collect($this->allFiles())
                    ->flatten()
                    ->sum(fn ($f) => $f->getSize()) / 1024 / 1024,
                2
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'action' => ['required', Rule::in(['draft', 'submit'])],

            'demande_id' => [
                'sometimes',
                'nullable',
                'exists:demandes,id',
            ],

            // -----------------------------
            // Informations du défunt
            // -----------------------------
            'nom_complet_defunt' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'numero_pension'     => ['nullable', 'required_if:action,submit', new CodePension()],

            // -----------------------------
            // Documents du défunt (obligatoires)
            // -----------------------------
            'certificat_carriere'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'acte_deces'                 => ['nullable', 'array'],
            'acte_deces.*'               => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'certificat_non_dissolution' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'carte_pension'              => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'souche_cheque'              => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // -----------------------------
            // Informations du bénéficiaire
            // -----------------------------
            'nom_beneficiaire' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'relation_defunt'  => ['nullable', 'required_if:action,submit'],

            // -----------------------------
            // Documents du bénéficiaire
            // -----------------------------
            'extrait_acte_mariage'   => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'extrait_acte_naissance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'matricule_fiscal'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'carte_electorale'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Photos (2 minimum)
            'photos_identites'        => ['nullable', 'array'],
            'photos_identites.*'      => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // -----------------------------
            // Documents optionnels
            // -----------------------------
            'pv_tutelle'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'certificat_medical'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'copie_moniteur'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'attestations_scolaires' => ['nullable', 'array'],
            'attestations_scolaires.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], 
        ];
    }

    /**
     * Noms lisibles des champs
     */
    public function attributes(): array
    {
        return [
            'nom_complet_defunt'     => 'nom complet du défunt',
            'numero_pension'        => 'numéro de pension',

            'certificat_carriere'   => 'certificat de carrière',
            'acte_deces'            => 'acte de décès',
            'certificat_non_dissolution' => 'certificat de non-dissolution',
            'carte_pension'         => 'carte de pension',
            'souche_cheque'         => 'souche de chèque',

            'nom_beneficiaire'      => 'nom du bénéficiaire',
            'relation_defunt'       => 'lien avec le défunt',

            'extrait_acte_mariage'  => 'extrait de l’acte de mariage',
            'extrait_acte_naissance'=> 'extrait de l’acte de naissance',
            'matricule_fiscal'      => 'matricule fiscal',
            'carte_electorale'      => 'carte électorale',
            'photos_identite'       => 'photos d’identité',

            'pv_tutelle'            => 'procès-verbal de tutelle',
            'certificat_medical'    => 'certificat médical',
            'copie_moniteur'        => 'copie du moniteur',
            'attestation_scolaires' => 'attestation scolaire',
        ];
    }
}
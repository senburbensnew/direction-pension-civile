<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandePensionReversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // -----------------------------
            // Informations du défunt
            // -----------------------------
            'nom_complet_defunt'      => ['required', 'string', 'max:255'],
            'numero_pension'     => ['required', 'string', 'max:100'],

            // -----------------------------
            // Documents du défunt (obligatoires)
            // -----------------------------
            'certificat_carriere'        => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'acte_deces'                 => ['required', 'array', 'min:2'],
            'acte_deces.*'               => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'certificat_non_dissolution' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'carte_pension'              => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'souche_cheque'              => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // -----------------------------
            // Informations du bénéficiaire
            // -----------------------------
            'nom_beneficiaire'   => ['required', 'string', 'max:255'],
            'relation_defunt'       => ['required'],

            // -----------------------------
            // Documents du bénéficiaire
            // -----------------------------
            'extrait_acte_mariage'   => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'extrait_acte_naissance' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'matricule_fiscal'       => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'carte_electorale'       => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Photos (2 minimum)
            'photos_identite'        => ['required', 'array', 'min:2'],
            'photos_identite.*'      => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // -----------------------------
            // Documents optionnels
            // -----------------------------
            'pv_tutelle'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'certificat_medical'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'copie_moniteur'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'attestation_scolaires' => ['nullable', 'array'],
            'attestation_scolaires.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],


            // -----------------------------
            // Consentement
            // -----------------------------
            'consentement'        => ['required', 'accepted']
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'file'     => 'Le champ :attribute doit être un fichier valide.',
            'image'    => 'Le fichier :attribute doit être une image.',
            'mimes'    => 'Le fichier :attribute doit être de type :values.',
            'max'      => 'Le fichier :attribute ne doit pas dépasser :max Ko.',
            'in'       => 'La valeur sélectionnée pour :attribute est invalide.',
            'photos_identite.min' => 'Veuillez fournir au moins deux (2) photos d’identité.',
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
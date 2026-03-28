<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandePensionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
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

            // Attestations de carrière
            'career_certificates'   => ['nullable', 'array'],
            'career_certificates.*' => ['file', 'mimes:pdf', 'max:5120'],

            // Dernier bulletin
            'monitor_copy' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Acte de mariage
            'marriage_certificates'   => ['nullable', 'array'],
            'marriage_certificates.*' => ['file', 'mimes:pdf', 'max:5120'],

            // Extrait récent de naissance
            'birth_certificates'   => ['nullable', 'array'],
            'birth_certificates.*' => ['file', 'mimes:pdf', 'max:5120'],

            // Divorce
            'divorce_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Matricule fiscal + CIN
            'tax_id_numbers'   => ['nullable', 'array'],
            'tax_id_numbers.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Photos
            'photos'   => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:1024'],

            // Certificat médical
            'medical_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Fiche de paie
            'check_stub' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'marriage_certificates.array' => 'L’acte de mariage doit être envoyé sous forme de fichiers.',
            'marriage_certificates.min'   => 'Si vous fournissez un acte de mariage, veuillez joindre au moins 2 fichiers (copie et original).',
            'marriage_certificates.*.mimes' => 'Les actes de mariage doivent être au format PDF.',
            'title.required' => 'Le titre est obligatoire.',
            'action.in' => "L'action doit être 'draft' ou 'submit'.",
        ];
    }

    /**
     * Traduction des noms des champs (attributs)
     */
    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'action'  => 'action',

            'career_certificates'   => 'attestation(s) de carrière',
            'career_certificates.*' => 'attestation de carrière',

            'monitor_copy' => 'copie du moniteur',

            'marriage_certificates'   => 'acte de mariage',
            'marriage_certificates.*' => 'fichier de l’acte de mariage',

            'birth_certificates'   => 'extrait récent de l’acte de naissance',
            'birth_certificates.*' => 'fichier de l’acte de naissance',

            'divorce_certificate' => 'acte de divorce',

            'tax_id_numbers'   => 'matricule fiscal et carte d’identification nationale (CIN)',
            'tax_id_numbers.*' => 'fichier du matricule fiscal ou de la CIN',

            'photos'   => 'photos d’identité',
            'photos.*' => 'photo d’identité',

            'medical_certificate' => 'certificat médical',

            'check_stub' => 'souche de chèque ou preuve de paiement',
        ];
    }
}
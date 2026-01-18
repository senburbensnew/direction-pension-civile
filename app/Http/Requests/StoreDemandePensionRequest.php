<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandePensionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nif'  => ['required', new Nif()],

            // Attestations de carrière
            'career_certificates'   => ['required', 'array', 'min:1'],
            'career_certificates.*' => ['file', 'mimes:pdf', 'max:5120'],

            // Dernier bulletin
            'monitor_copy' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Acte de mariage (optionnel mais min 2 si fourni)
            'marriage_certificates'   => ['nullable', 'array', 'min:2'],
            'marriage_certificates.*' => ['file', 'mimes:pdf', 'max:5120'],

            // Extrait récent de naissance
            'birth_certificates'   => ['required', 'array', 'min:2'],
            'birth_certificates.*' => ['file', 'mimes:pdf', 'max:5120'],

            // Divorce (optionnel)
            'divorce_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Matricule fiscal + CIN
            'tax_id_numbers'   => ['required', 'array', 'min:2'],
            'tax_id_numbers.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Photos
            'photos'   => ['required', 'array', 'min:2'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:1024'],

            // Certificat médical (optionnel)
            'medical_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // Fiche de paie
            'check_stub' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // -----------------------------
            // Consentement
            // -----------------------------
            'consentement'        => ['required', 'accepted']
        ];
    }

    public function messages(): array
    {
        return [
            'marriage_certificates.array' => 'L’acte de mariage doit être envoyé sous forme de fichiers.',
            'marriage_certificates.min'   => 'Si vous fournissez un acte de mariage, veuillez joindre au moins 2 fichiers (copie et original).',
            'marriage_certificates.*.mimes' => 'Les actes de mariage doivent être au format PDF.',
        ];
    }

    /**
     * Traduction des noms des champs (attributs)
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom complet',
            'nif'  => 'matricule',

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

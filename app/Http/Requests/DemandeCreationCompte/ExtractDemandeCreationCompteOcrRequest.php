<?php

namespace App\Http\Requests\DemandeCreationCompte;

use Illuminate\Foundation\Http\FormRequest;

class ExtractDemandeCreationCompteOcrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isMineur = $this->boolean('is_mineur');

        return [
            'is_mineur' => ['nullable', 'boolean'],

            'piece_identite_type' => [
                $isMineur ? 'nullable' : 'required',
                'nullable',
                'in:permis,passeport,cin,matricule_fiscal',
            ],

            'piece_identite' => [
                $isMineur ? 'nullable' : 'required',
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],

            'acte_naissance' => [
                $isMineur ? 'required' : 'nullable',
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],

            'representant_lien' => [
                $isMineur ? 'required' : 'nullable',
                'nullable',
                'in:pere,mere,tuteur',
            ],

            'piece_identite_representant_type' => [
                $isMineur ? 'required' : 'nullable',
                'nullable',
                'in:permis,passeport,cin',
            ],

            'piece_identite_representant' => [
                $isMineur ? 'required' : 'nullable',
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Le champ « :attribute » est obligatoire.',
            'mimes' => 'Le fichier « :attribute » doit être une image (JPG, PNG, WEBP) ou un PDF.',
            'in' => 'La valeur sélectionnée pour « :attribute » est invalide.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'piece_identite_type' => 'type de pièce d’identité',
            'piece_identite' => 'pièce d’identité du pensionné',
            'acte_naissance' => 'acte de naissance du pensionné mineur',
            'representant_lien' => 'lien du représentant',
            'piece_identite_representant_type' => 'type de pièce d’identité du représentant',
            'piece_identite_representant' => 'pièce d’identité du représentant',
        ];
    }
}

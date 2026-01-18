<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
use App\Rules\AnneeFiscale;
use Illuminate\Foundation\Http\FormRequest;

class StorePreuveExistenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // sécurité
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fin_pension' => $this->fin_pension ?: null,
        ]);
    }

    public function rules(): array
    {
        return [

            // Identification
            'numero_identite' => 'required|string|max:50',
            'profile_photo'  => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'annee_fiscale'   => ['required', new AnneeFiscale()],

            // Identité pensionné
            'nif'        => ['required', new Nif()],
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'adresse'    => 'required|string|max:255',
            'localisation' => 'required|string|max:150',

            'date_naissance' => 'required|date|before:today',
            'etat_civil_id'  => 'required|exists:civil_statuses,id',
            'sexe_id'        => 'required|exists:genders,id',

            // Contact
            'adresse_postale' => 'required|string|max:100',
            'telephone'       => ['required', new Telephone()],

            // Pension
            'montant_pension' => 'required|numeric|min:0',
            'no_moniteur'     => 'required|string|max:50',
            'date_moniteur'   => 'required|date',
            'debut_pension'   => 'required|date|before_or_equal:fin_pension',
            'fin_pension'     => 'nullable|date|after_or_equal:debut_pension',
            'categorie_pension_id' => 'required|exists:pension_categories,id',

            // Dépendants
            'dependants' => 'nullable|array|min:1',

            'dependants.*.nom'       => 'required|string|max:150',
            'dependants.*.relation'   => 'required|string|max:100',
            'dependants.*.date_naissance' => 'required|date|before:today',
            'dependants.*.sexe_id'  => 'required|exists:genders,id',
        ];
    }

    public function messages(): array
    {
        return [
            'required'  => 'Ce champ est obligatoire.',
            'annee_fiscale.regex' => 'Le format doit être YYYY/YYYY (ex: 2025/2026).',
        ];
    }
}

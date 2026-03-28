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
        return auth()->check();
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
            'title'                => 'nullable|string|max:255',
            'action'               => 'required|in:draft,submit',
            'demande_id'           => 'sometimes|nullable|exists:demandes,id',

            'numero_identite'      => ['nullable', 'string', 'max:50'],
            'profile_photo'        => ['nullable', 'sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'annee_fiscale'        => ['nullable', 'required_if:action,submit', new AnneeFiscale()],
            'nif'                  => ['nullable', 'required_if:action,submit', new Nif()],
            'nom'                  => ['nullable', 'required_if:action,submit', 'string', 'max:100'],
            'prenom'               => ['nullable', 'required_if:action,submit', 'string', 'max:100'],
            'adresse'              => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'localisation'         => ['nullable', 'string', 'max:150'],
            'date_naissance'       => ['nullable', 'required_if:action,submit', 'date', 'before:today'],
            'etat_civil_id'        => ['nullable', 'required_if:action,submit', 'exists:civil_statuses,id'],
            'sexe_id'              => ['nullable', 'required_if:action,submit', 'exists:genders,id'],
            'adresse_postale'      => ['nullable', 'string', 'max:100'],
            'telephone'            => ['nullable', 'required_if:action,submit', new Telephone()],
            'montant_pension'      => ['nullable', 'required_if:action,submit', 'numeric', 'min:0'],
            'no_moniteur'          => ['nullable', 'string', 'max:50'],
            'date_moniteur'        => ['nullable', 'date'],
            'debut_pension'        => ['nullable', 'required_if:action,submit', 'date'],
            'fin_pension'          => ['nullable', 'date', 'after_or_equal:debut_pension'],
            'categorie_pension_id' => ['nullable', 'required_if:action,submit', 'exists:pension_categories,id'],
            'dependants'           => 'nullable|array',
            'dependants.*.nom'           => 'nullable|string|max:150',
            'dependants.*.relation'      => 'nullable|string|max:100',
            'dependants.*.date_naissance'=> 'nullable|date|before:today',
            'dependants.*.sexe_id'       => 'nullable|exists:genders,id',
        ];
    }

    public function messages(): array
    {
        return [
            'required'             => 'Ce champ est obligatoire.',
            'annee_fiscale.regex'  => 'Le format doit être YYYY/YYYY (ex: 2025/2026).',
        ];
    }
}

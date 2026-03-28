<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
use App\Rules\CodePension;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeVirementBancaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'                => 'nullable|string|max:255',
            'action'               => 'required|in:draft,submit',
            'demande_id'           => 'sometimes|nullable|exists:demandes,id',

            'profile_photo'        => 'nullable|sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'code_pension'         => ['nullable', 'required_if:action,submit', new CodePension()],
            'type_pension_id'      => ['nullable', 'required_if:action,submit', 'exists:pension_types,id'],
            'nif'                  => ['nullable', new Nif()],
            'nom_complet'          => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'adresse'              => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'ville'                => ['nullable', 'string', 'max:255'],
            'date_naissance'       => ['nullable', 'required_if:action,submit', 'date'],
            'statut_civil_id'      => ['nullable', 'required_if:action,submit', 'exists:civil_statuses,id'],
            'sexe_id'              => ['nullable', 'required_if:action,submit', 'exists:genders,id'],
            'montant_allocation'   => ['nullable', 'numeric', 'min:0'],
            'nom_mere'             => ['nullable', 'string', 'max:255'],
            'telephone'            => ['nullable', 'required_if:action,submit', new Telephone()],
            'categorie_pension_id' => ['nullable', 'required_if:action,submit', 'exists:pension_categories,id'],
            'nom_banque'           => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'numero_compte'        => ['nullable', 'required_if:action,submit', 'numeric', 'digits_between:5,20'],
            'nom_compte'           => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'profile_photo'        => 'photo de profil',
            'code_pension'         => 'code du pensionné',
            'type_pension_id'      => 'type de pension',
            'nif'                  => 'NIF',
            'nom_complet'          => 'nom et prénom(s)',
            'adresse'              => 'adresse',
            'ville'                => 'ville',
            'date_naissance'       => 'date de naissance',
            'statut_civil_id'      => 'état civil',
            'sexe_id'              => 'sexe',
            'montant_allocation'   => 'montant de l\'allocation',
            'nom_mere'             => 'nom de la mère',
            'telephone'            => 'numéro de téléphone',
            'categorie_pension_id' => 'catégorie de pension',
            'nom_banque'           => 'nom de la banque',
            'numero_compte'        => 'numéro de compte',
            'nom_compte'           => 'nom du compte bancaire',
        ];
    }

    public function messages(): array
    {
        return [
            'required'                     => 'Le champ :attribute est obligatoire.',
            'profile_photo.image'          => 'La :attribute doit être une image valide.',
            'profile_photo.mimes'          => 'La :attribute doit être au format JPEG, PNG, JPG, GIF ou WEBP.',
            'profile_photo.max'            => 'La :attribute ne doit pas dépasser 2 Mo.',
            'string'                       => 'Le champ :attribute doit être un texte valide.',
            'max'                          => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'date_naissance.date'          => 'La :attribute doit être une date valide.',
            'exists'                       => 'La valeur sélectionnée pour :attribute est invalide.',
            'numeric'                      => 'Le champ :attribute doit être un nombre valide.',
            'montant_allocation.min'       => 'Le :attribute doit être supérieur ou égal à zéro.',
            'numero_compte.digits_between' => 'Le :attribute doit contenir entre :min et :max chiffres.',
        ];
    }
}

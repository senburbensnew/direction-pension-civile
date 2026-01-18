<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
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
            'profile_photo'        => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            'code_pension'       => 'required|string',

            'type_pension_id'      => 'required|exists:pension_types,id',

            'nif'                  => ['required', new Nif()],

            'nom_complet'            => 'required|string|max:255',

            'adresse'              => 'required|string|max:255',

            'ville'                 => 'required|string|max:255',

            'date_naissance'           => 'required|date',

            'statut_civil_id'      => 'required|exists:civil_statuses,id',

            'sexe_id'            => 'required|exists:genders,id',

            'montant_allocation'    => 'required|numeric|min:0',

            'nom_mere'          => 'required|string|max:255',

            'telephone'         => [
               'required',
                new Telephone()
            ],

            'categorie_pension_id'  => 'required|exists:pension_categories,id',

            'nom_banque'            => 'required|string|max:255',

            'numero_compte'       => 'required|numeric|digits_between:5,20',

            'nom_compte'         => 'required|string|max:255',

            'consentement' => ['required', 'accepted'],
        ];
    }

    /**
     * Noms lisibles des champs
     */
    public function attributes(): array
    {
        return [
            'profile_photo'        => 'photo de profil',
            'code_pension'       => 'code du pensionné',
            'type_pension_id'      => 'type de pension',
            'nif'                  => 'NIF',
            'nom_complet'            => 'nom et prénom(s)',
            'adresse'              => 'adresse',
            'ville'                 => 'ville',
            'date_naissance'           => 'date de naissance',
            'statut_civil_id'      => 'état civil',
            'sexe_id'            => 'sexe',
            'montant_allocation'    => 'montant de l’allocation',
            'nom_mere'          => 'nom de la mère',
            'telephone'                => 'numéro de téléphone',
            'categorie_pension_id'  => 'catégorie de pension',
            'nom_banque'            => 'nom de la banque',
            'numero_compte'       => 'numéro de compte',
            'nom_compte'         => 'nom du compte bancaire',
            'consentement' => 'déclaration et engagement',
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [

            /* REQUIRED */
            'required' => 'Le champ :attribute est obligatoire.',

            /* IMAGE */
            'profile_photo.image'  => 'La :attribute doit être une image valide.',
            'profile_photo.mimes'  => 'La :attribute doit être au format JPEG, PNG, JPG, GIF ou WEBP.',
            'profile_photo.max'    => 'La :attribute ne doit pas dépasser 2 Mo.',

            /* STRING / LENGTH */
            'string'    => 'Le champ :attribute doit être un texte valide.',
            'max'       => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'min'       => 'Le champ :attribute doit contenir au moins :min caractères.',

            /* DATE */
            'date_naissance.date' => 'La :attribute doit être une date valide.',

            /* EXISTS (SELECT / RADIO) */
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',

            /* NUMERIC */
            'numeric' => 'Le champ :attribute doit être un nombre valide.',
            'montant_allocation.min' => 'Le :attribute doit être supérieur ou égal à zéro.',

            /* ACCOUNT NUMBER */
            'numero_compte.digits_between' =>
                'Le :attribute doit contenir entre :min et :max chiffres.',

            /* DECLARATION */
            'consentement.accepted' =>
                'Vous devez accepter la déclaration et l’engagement pour continuer.',
        ];
    }
}

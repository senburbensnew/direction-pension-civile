<?php

namespace App\Http\Requests;

use App\Helpers\RegexExpressions;
use App\Rules\Cin;
use App\Rules\NifNinu;
use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEtatCarriereRequest extends FormRequest
{
    /**
     * Autorisation
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'action' => ['required', Rule::in(['draft', 'submit'])],

            'demande_id' => [
                'sometimes',
                'nullable',
                'required_if:action,submit',
                'exists:demandes,id',
            ],

            // ================= INFORMATIONS PERSONNELLES =================
            'nom'               => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'prenom'            => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'nom_jeune_fille'   => ['nullable', 'string', 'max:255'],
            'date_naissance'    => ['nullable', 'required_if:action,submit', 'date', 'before_or_equal:today'],
            'lieu_naissance'    => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'etat_civil'        => ['nullable', 'required_if:action,submit', 'in:celibataire,marie,veuf,divorce'],

            // ================= IDENTIFICATION =================
            'nif_ninu'          => [
                'nullable', 'required_if:action,submit', 
                new NifNinu()
            ],
            'cin'               => ['nullable', 'required_if:action,submit', new Cin()],

            // ================= INFORMATIONS PROFESSIONNELLES =================
            'statut'            => ['nullable', 'required_if:action,submit', 'in:fonctionnaire,contractuel,salarie,pensionne'],
            'employeur'         => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'fonction'          => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'date_debut_service'=> ['nullable', 'required_if:action,submit', 'date', 'before_or_equal:today'],
            'date_fin_service'  => ['nullable', 'required_if:action,submit', 'date', 'after_or_equal:date_debut_service'],
            'numero_dossier'    => ['nullable', 'required_if:action,submit', 'string', 'max:100'],

            // ================= COORDONNÉES =================
            'adresse'           => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'telephone'         => ['nullable', 'required_if:action,submit', new Telephone()],
            'email'             => ['nullable', 'required_if:action,submit', 'email', 'max:255'],

            // ================= PIÈCES JOINTES =================
            'copie_piece_identite' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'lettre_nomination'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'bulletins_salaire'     => ['nullable', 'array'],
            'bulletins_salaire.*'   => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'documents_carriere'    => ['nullable', 'array'],
            'documents_carriere.*'  => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'acte_mariage_acte_deces' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // ================= OBJET =================
            'raison'            => ['nullable', 'required_if:action,submit', 'string', 'min:10']
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [

            'required'  => 'Ce champ est obligatoire.',
            'email'     => 'Veuillez fournir une adresse email valide.',
            'date'      => 'Veuillez fournir une date valide.',
            'before_or_equal' => 'La date ne peut pas être postérieure à aujourd’hui.',
            'after_or_equal'  => 'La date de fin doit être postérieure à la date de début.',
            'in'        => 'Valeur sélectionnée invalide.',
            'accepted'  => 'Vous devez accepter cette déclaration pour continuer.',

            'copie_pièce_identite.required' => 'La copie de la pièce d’identité est obligatoire.',
            'raison.min' => 'Le motif doit contenir au moins :min caractères.',

            'mimes'     => 'Le fichier doit être au format PDF, JPG ou PNG.',
            'max'       => 'La taille du fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}

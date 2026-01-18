<?php

namespace App\Http\Requests;

use App\Rules\Cin;
use App\Rules\NifNinu;
use App\Rules\Telephone;
use App\Helpers\RegexExpressions;
use Illuminate\Foundation\Http\FormRequest;

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

            // ================= INFORMATIONS PERSONNELLES =================
            'nom'               => ['required', 'string', 'max:255'],
            'prenom'            => ['required', 'string', 'max:255'],
            'nom_jeune_fille'   => ['nullable', 'string', 'max:255'],
            'date_naissance'    => ['required', 'date', 'before_or_equal:today'],
            'lieu_naissance'    => ['required', 'string', 'max:255'],
            'etat_civil'        => ['required', 'in:celibataire,marie,veuf,divorce'],

            // ================= IDENTIFICATION =================
            'nif_ninu'          => [
                'required', 
                new NifNinu()
            ],
            'cin'               => ['required', new Cin()],

            // ================= INFORMATIONS PROFESSIONNELLES =================
            'statut'            => ['required', 'in:fonctionnaire,contractuel,salarie,pensionne'],
            'employeur'         => ['required', 'string', 'max:255'],
            'fonction'          => ['required', 'string', 'max:255'],
            'date_debut_service'=> ['required', 'date', 'before_or_equal:today'],
            'date_fin_service'  => ['nullable', 'date', 'after_or_equal:date_debut_service'],
            'numero_dossier'    => ['nullable', 'string', 'max:100'],

            // ================= COORDONNÉES =================
            'adresse'           => ['required', 'string', 'max:255'],
            'telephone'         => ['required', new Telephone()],
            'email'             => ['required', 'email', 'max:255'],

            // ================= PIÈCES JOINTES =================
            'copie_piece_identite' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'lettre_nomination'    => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'bulletins_salaire'     => ['required', 'array'],
            'bulletins_salaire.*'   => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'documents_carriere'    => ['required', 'array'],
            'documents_carriere.*'  => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'acte_mariage_acte_deces' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // ================= OBJET =================
            'raison'            => ['required', 'string', 'min:10'],

            // ================= CONSENTEMENT =================
            'consentement'      => ['required', 'accepted'],
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

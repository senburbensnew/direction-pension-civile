<?php

namespace App\Http\Requests;

use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeArretVirementRequest extends FormRequest
{
    /**
     * Autorisation de la requête
     */
    public function authorize(): bool
    {
        // Accessible si authentifié
        // ou mets true si accès public
        return auth()->check();
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            // Informations générales
            'date' => ['required', 'date'],
            'code' => ['required', 'string', 'max:50'],

            // Informations personnelles
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', new Telephone()],
            'courriel' => ['required', 'email', 'max:150'],

            // Virement
            'mois_non_recu' => ['required', 'string', 'max:100'],
            'motifs' => ['required', 'array'],
            'motifs.*' => ['string', 'max:100'],

            'nouveau_numero' => ['nullable', 'string', 'max:50'],
            'nom_du_compte' => ['nullable', 'string', 'max:150'],

            // Chèques
            'cheques' => ['required', 'string', 'max:1000'],

            // Informations transmises
            'informations' => ['required', 'string', 'max:1000'],

            // Déclaration
            'consentement' => ['required', 'accepted'],
        ];
    }

    /**
     * Messages d’erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'date.date' => 'La date n’est pas valide.',
            'courriel.email' => 'Le courriel doit être valide.',
            'motifs.array' => 'Les motifs doivent être une liste valide.',
            'consentement.accepted' => 'Vous devez certifier l’exactitude des informations.',
        ];
    }

    /**
     * Noms lisibles des champs
     */
    public function attributes(): array
    {
        return [
            'date' => 'date',
            'code' => 'code',
            'nom' => 'nom',
            'prenom' => 'prénom',
            'telephone' => 'téléphone',
            'courriel' => 'courriel',
            'mois_non_recu' => 'mois non reçus',
            'motifs' => 'motifs identifiés',
            'nouveau_numero' => 'nouveau numéro de compte',
            'nom_du_compte' => 'nom du compte',
            'cheques' => 'réclamation de chèques',
            'informations' => 'informations transmises',
            'consentement' => 'déclaration',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
use App\Rules\AnneeFiscale;
use App\Models\PensionCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransfertChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // OK
    }

    public function rules(): array
    {
        $validPensionCategories = PensionCategory::pluck('id')->toArray();

        return [
            'annee_fiscale' => ['required', new AnneeFiscale()],
            'mois_debut' => 'required|string|size:7', // YYYY-MM
            'date_demande' => 'required|date',

            'categorie_pension_id' => 'required|in:' . implode(',', $validPensionCategories),
            'code_pension' => 'required|string|max:255',
            'montant' => 'required|numeric',

            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'nom_jeune_fille' => 'required|string|max:255',

            'nif' => ['required', new Nif()],
            'ninu' => 'required|string|max:255',

            'adresse' => 'required|string|max:255',
            'telephone' => ['required', new Telephone()],
            'email' => 'required|email|max:255',

            'de' => 'required|date',
            'a' => 'required|date|after_or_equal:de',

            'raison_transfert' => 'required|string|max:1000',
            'consentement' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'numeric' => 'Le :attribute doit être un nombre valide.',
            'digits' => 'Le :attribute doit contenir exactement :digits chiffres.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'email' => 'Veuillez fournir une adresse email valide.',
            'in' => 'La valeur sélectionnée pour :attribute est invalide.',
            'after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            /* DECLARATION */
            'consentement.accepted' => 'Vous devez accepter la déclaration et l’engagement pour continuer.',
        ];
    }

    public function attributes(): array
    {
        return [
            'annee_fiscale' => 'année fiscale',
            'mois_debut' => 'mois de début',
            'date_demande' => 'date de la demande',

            'categorie_pension_id' => 'régime de pension',
            'code_pension' => 'code du pensionné',
            'montant' => 'montant d’allocation',

            'nom' => 'nom',
            'prenom' => 'prénom',
            'nom_jeune_fille' => 'nom de jeune fille',

            'nif' => 'NIF',
            'ninu' => 'NINU',

            'adresse' => 'adresse',
            'telephone' => 'téléphone',
            'email' => 'courriel',

            'de' => 'début de période',
            'a' => 'fin de période',

            'raison_transfert' => 'motif du transfert',
            'consentement' => 'déclaration et engagement',
        ];
    }
}
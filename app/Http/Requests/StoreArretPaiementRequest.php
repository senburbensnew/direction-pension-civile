<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
use App\Helpers\RegexExpressions;
use Illuminate\Foundation\Http\FormRequest;

class StoreArretPaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Informations générales
            'exercice'       => 'required|string|max:9',
            'mois_debut'     => 'required|date_format:Y-m',
            'date_demande'   => 'required|date',

            // Régime
            'regime_pension' => 'required|in:civile,militaire',

            // Pensionné
            'code_pension'   => 'required|string|max:50',
            'montant'        => 'required|numeric|min:0',

            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'nom_jeune_fille'=> 'nullable|string|max:100',

            'nif'            => ['required', new Nif()],
            'ninu'           => 'required|string|max:20',
            'telephone'      => ['required', new Telephone()],

            'adresse'        => 'required|string|max:255',
            'email'          => 'required|email|max:255',

            // Période
            'periode_debut'  => 'required|date',
            'periode_fin'    => 'required|date|after_or_equal:periode_debut',

            // Pièces justificatives
            'pieces'         => 'required|array|min:1',
            'pieces.*'       => 'file|mimes:pdf,jpg,jpeg,png|max:5120',

            // Consentement
            'consentement'   => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'accepted' => 'Vous devez accepter la déclaration.',
            'after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'pieces.required' => 'Veuillez joindre au moins une pièce justificative.',
            'pieces.*.mimes' => 'Les fichiers doivent être en PDF, JPG ou PNG.',
            'pieces.*.max' => 'Chaque fichier ne doit pas dépasser 5 Mo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'exercice' => 'exercice',
            'mois_debut' => 'mois de début',
            'date_demande' => 'date de la demande',
            'regime_pension' => 'régime de pension',
            'code_pension' => 'code pension',
            'periode_debut' => 'date de début',
            'periode_fin' => 'date de fin',
            'pieces' => 'pièces justificatives',
            'consentement' => 'déclaration',
        ];
    }
}

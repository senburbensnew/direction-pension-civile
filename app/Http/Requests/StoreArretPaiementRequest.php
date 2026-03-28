<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Telephone;
use App\Rules\CodePension;
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
            'title'          => 'nullable|string|max:255',
            'action'         => 'required|in:draft,submit',
            'demande_id'     => 'sometimes|nullable|exists:demandes,id',

            'exercice'       => ['nullable', 'string', 'max:9'],
            'mois_debut'     => ['nullable', 'required_if:action,submit', 'date_format:Y-m'],
            'date_demande'   => ['nullable', 'required_if:action,submit', 'date'],
            'regime_pension' => ['nullable', 'required_if:action,submit', 'in:civile,militaire'],
            'code_pension'   => ['nullable', 'required_if:action,submit', new CodePension()],
            'montant'        => ['nullable', 'numeric', 'min:0'],
            'nom'            => ['nullable', 'required_if:action,submit', 'string', 'max:100'],
            'prenom'         => ['nullable', 'required_if:action,submit', 'string', 'max:100'],
            'nom_jeune_fille'=> ['nullable', 'string', 'max:100'],
            'nif'            => ['nullable', 'required_if:action,submit', new Nif()],
            'ninu'           => ['nullable', 'string', 'max:20'],
            'telephone'      => ['nullable', 'required_if:action,submit', new Telephone()],
            'adresse'        => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'periode_debut'  => ['nullable', 'required_if:action,submit', 'date'],
            'periode_fin'    => ['nullable', 'required_if:action,submit', 'date', 'after_or_equal:periode_debut'],
            'pieces'         => ['nullable', 'array'],
            'pieces.*'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'            => 'Le champ :attribute est obligatoire.',
            'after_or_equal'      => 'La date de fin doit être postérieure ou égale à la date de début.',
            'pieces.*.mimes'      => 'Les fichiers doivent être en PDF, JPG ou PNG.',
            'pieces.*.max'        => 'Chaque fichier ne doit pas dépasser 5 Mo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'exercice'       => 'exercice',
            'mois_debut'     => 'mois de début',
            'date_demande'   => 'date de la demande',
            'regime_pension' => 'régime de pension',
            'code_pension'   => 'code pension',
            'periode_debut'  => 'date de début',
            'periode_fin'    => 'date de fin',
            'pieces'         => 'pièces justificatives',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandePensionPensionnaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'      => ['required', 'string', 'max:255'],
            'action'     => ['required', Rule::in(['draft', 'submit'])],
            'demande_id' => [
                'sometimes',
                'nullable',
                'exists:demandes,id',
            ],

            'nif'          => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'nom'          => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'prenom'       => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'nom_complet'  => ['nullable', 'string', 'max:255'],
            'telephone'    => ['nullable', 'required_if:action,submit', 'string', 'max:50'],
            'email'        => ['nullable', 'email', 'max:255'],

            'date_naissance' => ['nullable', 'required_if:action,submit', 'date'],
            'lieu_naissance' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],

            'departement' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'commune'     => ['nullable', 'string', 'max:255'],
            'adresse'     => ['nullable', 'required_if:action,submit', 'string', 'max:255'],

            'cin'                  => ['nullable', 'required_if:action,submit', 'string', 'max:100'],
            'date_delivrance_cin'  => ['nullable', 'required_if:action,submit', 'date'],
            'lieu_delivrance_cin'  => ['nullable', 'required_if:action,submit', 'string', 'max:255'],

            'passeport'                  => ['nullable', 'string', 'max:100'],
            'date_delivrance_passeport'  => ['nullable', 'date'],
            'lieu_delivrance_passeport'  => ['nullable', 'string', 'max:255'],
            'date_expiration_passeport'  => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title'      => 'titre',
            'action'     => 'action',
            'demande_id' => 'identifiant de la demande',

            'nif'         => 'NIF',
            'nom'         => 'nom',
            'prenom'      => 'prénom',
            'nom_complet' => 'nom complet',
            'telephone'   => 'téléphone',
            'email'       => 'email',

            'date_naissance' => 'date de naissance',
            'lieu_naissance' => 'lieu de naissance',

            'departement' => 'département',
            'commune'     => 'commune',
            'adresse'     => 'adresse',

            'cin'                 => 'CIN',
            'date_delivrance_cin' => 'date de délivrance CIN',
            'lieu_delivrance_cin' => 'lieu de délivrance CIN',

            'passeport'                 => 'passeport',
            'date_delivrance_passeport' => 'date de délivrance du passeport',
            'lieu_delivrance_passeport' => 'lieu de délivrance du passeport',
            'date_expiration_passeport' => 'date d\'expiration du passeport',
        ];
    }
}

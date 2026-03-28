<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Ninu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandeAdhesionRequest extends FormRequest
{
    /**
     * Autorisation
     */
    public function authorize(): bool
    {
        return auth()->check(); // ou true si accès public
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
                'exists:demandes,id',
            ],

            'institution'           => ['nullable', 'string', 'max:255', 'required_if:action,submit',],
            'lastname'              => ['nullable', 'string', 'max:255', 'required_if:action,submit',],
            'firstname'             => ['nullable', 'string', 'max:255', 'required_if:action,submit',],
            'mother_lastname'       => ['nullable', 'string', 'max:255', 'required_if:action,submit',],
            'mother_firstname'      => ['nullable', 'string', 'max:255', 'required_if:action,submit',],
            'birth_place'           => ['nullable', 'string', 'max:255', 'required_if:action,submit',],
            'birth_date'            => ['nullable', 'date', 'before:today', 'required_if:action,submit',],

            'nif'                   => ['nullable', new Nif(), 'required_if:action,submit',],
            'ninu'                  => ['nullable', new Ninu(), 'required_if:action,submit',],

            'gender_id'             => ['nullable', 'integer', 'required_if:action,submit',],
            'civil_status_id'       => ['nullable', 'integer', 'required_if:action,submit',],

            'spouse_lastname'       => ['nullable', 'string', 'max:255'],
            'spouse_firstname'      => ['nullable', 'string', 'max:255'],

            'profile_photo'       => ['nullable', 'image', 'max:2048'],

            'dependents'                         => ['nullable', 'array'],
            'dependents.*.lastname'              => ['required', 'string', 'max:255'],
            'dependents.*.firstname'             => ['required', 'string', 'max:255'],
            'dependents.*.birthdate'             => ['required', 'date', 'before:today'],
            'dependents.*.relation'              => ['required', 'in:fils,fille'],

            'previous_jobs'                      => ['nullable', 'array'],
            'previous_jobs.*.institution'        => ['required', 'string', 'max:255'],
            'previous_jobs.*.start_date'         => ['required', 'date'],
            'previous_jobs.*.end_date'           => ['nullable', 'date', 'after_or_equal:previous_jobs.*.start_date'],

            'entry_date'            => ['nullable', 'date', 'required_if:action,submit',],
            'current_salary'        => ['nullable', 'numeric', 'min:0', 'required_if:action,submit',],
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'required' => 'Ce champ est obligatoire.',
            'before'   => 'La date doit être antérieure à aujourd’hui.',
            'numeric'  => 'Ce champ doit être un nombre valide.',
            'min'      => 'La valeur doit être positive.',
        ];
    }
}

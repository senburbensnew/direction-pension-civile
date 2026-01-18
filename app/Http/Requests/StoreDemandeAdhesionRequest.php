<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\Ninu;
use App\Helpers\RegexExpressions;
use Illuminate\Foundation\Http\FormRequest;

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
            // =========================
            // Informations personnelles
            // =========================
            'institution'           => ['required', 'string', 'max:255'],
            'lastname'              => ['required', 'string', 'max:255'],
            'firstname'             => ['required', 'string', 'max:255'],
            'mother_lastname'       => ['required', 'string', 'max:255'],
            'mother_firstname'      => ['required', 'string', 'max:255'],
            'birth_place'           => ['required', 'string', 'max:255'],
            'birth_date'            => ['required', 'date', 'before:today'],

            'nif'                   => ['required', new Nif()],
            'ninu'                  => ['required', new Ninu()],

            'gender_id'             => ['required', 'integer'],
            'civil_status_id'       => ['required', 'integer'],

            'spouse_lastname'       => ['nullable', 'string', 'max:255'],
            'spouse_firstname'      => ['nullable', 'string', 'max:255'],

            'profile_photo'       => ['required', 'image', 'max:2048'],

            // =========================
            // Enfants à charge
            // =========================
            'dependents'                         => ['nullable', 'array'],
            'dependents.*.lastname'              => ['required', 'string', 'max:255'],
            'dependents.*.firstname'             => ['required', 'string', 'max:255'],
            'dependents.*.birthdate'             => ['required', 'date', 'before:today'],
            'dependents.*.relation'              => ['required', 'in:fils,fille'],

            // =========================
            // Emplois antérieurs
            // =========================
            'previous_jobs'                      => ['nullable', 'array'],
            'previous_jobs.*.institution'        => ['required', 'string', 'max:255'],
            'previous_jobs.*.start_date'         => ['required', 'date'],
            'previous_jobs.*.end_date'           => ['nullable', 'date', 'after_or_equal:previous_jobs.*.start_date'],

            // =========================
            // Informations professionnelles
            // =========================
            'entry_date'            => ['required', 'date'],
            'current_salary'        => ['required', 'numeric', 'min:0'],

            // =========================
            // Consentement
            // =========================
            'consentement'          => ['required', 'accepted'],
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'required' => 'Ce champ est obligatoire.',
            'accepted' => 'Vous devez accepter cette déclaration.',
            'before'   => 'La date doit être antérieure à aujourd’hui.',
            'numeric'  => 'Ce champ doit être un nombre valide.',
            'min'      => 'La valeur doit être positive.',
        ];
    }
}

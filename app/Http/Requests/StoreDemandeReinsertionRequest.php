<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeReinsertionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'      => 'nullable|string|max:255',
            'action'     => 'required|in:draft,submit',
            'demande_id' => 'sometimes|nullable|exists:demandes,id',
            'prenom' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'nom'    => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'raison' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'    => 'Le champ :attribute est obligatoire.',
            'required_if' => 'Le champ :attribute est obligatoire pour soumettre la demande.',
        ];
    }

    public function attributes(): array
    {
        return [
            'prenom' => 'Prénom',
            'nom'    => 'Nom',
            'raison' => 'Motif',
        ];
    }
}

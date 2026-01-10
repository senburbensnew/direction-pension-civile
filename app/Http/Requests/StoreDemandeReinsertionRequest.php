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
            'prenom' => 'required|string|max:255',
            'nom'  => 'required|string|max:255',
            'raison'    => 'required|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'prenom' => 'Prénom',
            'nom'  => 'Nom',
            'raison'    => 'Motif',
        ];
    }
}

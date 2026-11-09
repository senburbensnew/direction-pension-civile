<?php

namespace App\Http\Requests\DemandeCreationCompte;

use Illuminate\Foundation\Http\FormRequest;

class AcceptDemandeCreationCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:8|max:72',
        ];
    }
}

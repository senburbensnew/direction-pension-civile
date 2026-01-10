<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\RegexExpressions;

class StoreDemandeAttestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code_pension' => ['required', 'string'],
            'nif'            => ['required', 'regex:' . RegexExpressions::nif()],
            'prenom'      => ['required', 'string', 'max:255'],
            'nom'       => ['required', 'string', 'max:255'],
            'consentement'   => ['required', 'accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code_pension' => 'code du pensionné',
            'nif'            => 'NIF',
            'prenom'      => 'Prénom',
            'nom'       => 'Nom',
            'consentement'   => 'déclaration et engagement',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'regex'    => 'Le format du champ :attribute est invalide.',
            'accepted' => 'Vous devez accepter la :attribute.',
        ];
    }
}
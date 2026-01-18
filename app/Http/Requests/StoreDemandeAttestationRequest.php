<?php

namespace App\Http\Requests;

use App\Rules\Nif;
use App\Rules\CodePension;
use App\Helpers\RegexExpressions;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeAttestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code_pension' => ['required', new CodePension()],
            'nif'            => ['required', new Nif()],
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
            'accepted' => 'Vous devez accepter la :attribute.',
        ];
    }
}
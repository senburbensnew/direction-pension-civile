<?php

namespace App\Http\Requests\DemandeCreationCompte;

use App\Rules\CodePension;
use App\Rules\Nif;
use App\Rules\Ninu;
use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMonDemandeCreationCompteRequest extends FormRequest
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
            'telephone' => ['required', 'string', new Telephone],
            'adresse' => 'required|string|max:500',
            'nif' => ['nullable', 'string', new Nif],
            'ninu' => ['nullable', 'string', new Ninu],
            'pension_code' => ['nullable', 'string', new CodePension],
            'piece_identite_type' => [
                'nullable',
                'in:permis,passeport,cin',
                Rule::requiredIf(fn () => $this->hasFile('piece_identite')),
            ],
            'piece_identite' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
                Rule::requiredIf(fn () => $this->filled('piece_identite_type')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'firstname' => 'prénom',
            'lastname' => 'nom',
            'email' => 'adresse e-mail',
            'telephone' => 'téléphone',
            'adresse' => 'adresse',
            'nif' => 'matricule fiscal (NIF)',
            'ninu' => 'NINU',
            'pension_code' => 'code pension',
        ];
    }
}

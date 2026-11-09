<?php

namespace App\Http\Requests\Formalites;

use Illuminate\Foundation\Http\FormRequest;

class RefuseBeneficiaireRelationDemandeRequest extends FormRequest
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
            'refusal_reason' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}

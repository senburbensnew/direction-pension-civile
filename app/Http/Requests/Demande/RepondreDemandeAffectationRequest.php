<?php

namespace App\Http\Requests\Demande;

use Illuminate\Foundation\Http\FormRequest;

class RepondreDemandeAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avis' => 'nullable|string|max:3000',
            'statut' => 'required|in:EN_COURS,TERMINE,REJETE',
        ];
    }
}

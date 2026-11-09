<?php

namespace App\Http\Requests\Demande;

use Illuminate\Foundation\Http\FormRequest;

class AffecterDemandeServicesRequest extends FormRequest
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
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'integer|exists:services,id',
        ];
    }
}

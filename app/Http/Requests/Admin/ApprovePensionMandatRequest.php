<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePensionMandatRequest extends FormRequest
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
            'allowed_demande_types' => ['nullable', 'array'],
            'allowed_demande_types.*' => ['string', 'max:64'],
            'allowed_pension_codes' => ['nullable', 'array'],
            'allowed_pension_codes.*' => ['string', 'max:32'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePensionContextRequest extends FormRequest
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
            'beneficiaire_id' => ['required', 'integer', 'exists:beneficiaires,id'],
            'affiliation_id' => ['nullable', 'integer', 'exists:affiliation_pensions,id'],
        ];
    }
}

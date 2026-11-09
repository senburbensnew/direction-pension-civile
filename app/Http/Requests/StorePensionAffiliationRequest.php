<?php

namespace App\Http\Requests;

use App\Rules\CodePension;
use App\Rules\PensionnaireReferenceExists;
use Illuminate\Foundation\Http\FormRequest;

class StorePensionAffiliationRequest extends FormRequest
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
            'code_pension' => [
                'required',
                'string',
                new CodePension(),
                new PensionnaireReferenceExists(),
            ],
            'is_primary' => ['sometimes', 'boolean'],
            'loc_description' => ['required', 'string', 'max:255', 'regex:/\S/'],
        ];
    }
}

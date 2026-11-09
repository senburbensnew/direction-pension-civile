<?php

namespace App\Http\Requests;

use App\Rules\CodePension;
use Illuminate\Foundation\Http\FormRequest;

class StoreBeneficiaireRelationDemandeRequest extends FormRequest
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
            'code_pension' => ['required', 'string', new CodePension],
            'type_relation' => ['required', 'string', 'in:pere,mere,tuteur'],
            'motif' => ['nullable', 'string', 'max:2000'],
            'justificatif' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}

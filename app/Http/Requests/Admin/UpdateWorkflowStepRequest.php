<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:150'],
            'service_id' => ['nullable', 'exists:services,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'ordre' => ['required', 'integer', 'min:0'],
            'type_noeud' => ['required', 'string', 'in:initial,intermediaire,terminal'],
            'type_demande' => ['nullable', 'string', 'max:100'],
        ];
    }
}

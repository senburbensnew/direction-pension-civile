<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_]+$/'],
            'service_id' => ['nullable', 'exists:services,id'],
            'type_demande' => ['nullable', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'ordre' => ['required', 'integer', 'min:0'],
            'type_noeud' => ['required', 'string', 'in:initial,intermediaire,terminal'],
        ];
    }
}

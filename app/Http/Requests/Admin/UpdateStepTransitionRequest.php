<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStepTransitionRequest extends FormRequest
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
            'action' => 'required|string|max:100',
            'is_urgent_only' => 'boolean',
            'type_demande' => 'nullable|string|max:100',
        ];
    }
}

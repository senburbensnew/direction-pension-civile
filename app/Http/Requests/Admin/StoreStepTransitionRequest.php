<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStepTransitionRequest extends FormRequest
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
            'from_step_id' => 'nullable|exists:workflow_steps,id',
            'to_step_id' => 'required|exists:workflow_steps,id|different:from_step_id',
            'action' => 'required|string|max:100',
            'is_urgent_only' => 'boolean',
            'type_demande' => 'nullable|string|max:100',
        ];
    }
}

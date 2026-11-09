<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOcrTestRequest extends FormRequest
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
            'document' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document.required' => 'Choisissez un fichier à analyser.',
            'document.mimes' => 'Formats acceptés : JPG, PNG, WEBP ou PDF.',
        ];
    }
}

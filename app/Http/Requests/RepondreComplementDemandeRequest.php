<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepondreComplementDemandeRequest extends FormRequest
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
            'message' => 'required|string|max:3000',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ];
    }
}

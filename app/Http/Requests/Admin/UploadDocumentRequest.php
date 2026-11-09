<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pdf_name' => ['required', 'string'],
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'pdf_file.mimes' => 'Le fichier doit être un document PDF.',
            'pdf_file.max' => 'Le fichier ne doit pas dépasser 2MB.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\PublicationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(array_keys(PublicationType::options()))],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'url' => ['nullable', 'url', 'max:500'],
            'order_column' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ];
    }
}

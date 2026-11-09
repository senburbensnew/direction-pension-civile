<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGlossaireTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'term' => ['required', 'string', 'max:200'],
            'definition' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'order_column' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ];
    }
}

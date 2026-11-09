<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:100', 'unique:officials,slug', 'regex:/^[a-z0-9\-]+$/'],
            'role' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'sexe' => ['required', 'in:M,F'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'biographie' => ['nullable', 'string'],
            'discours' => ['nullable', 'string'],
            'citation' => ['nullable', 'string', 'max:500'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ];
    }
}

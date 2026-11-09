<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDirectionDepartementaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'abbr' => ['required', 'string', 'max:20', 'unique:direction_departementales,abbr'],
            'nom' => ['required', 'string', 'max:255'],
            'ville' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:3000'],
            'color' => ['required', 'string', 'max:30'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

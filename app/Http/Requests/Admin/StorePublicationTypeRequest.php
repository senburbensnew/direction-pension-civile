<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:publication_types,code'],
            'icon' => ['nullable', 'string', 'max:50'],
            'badge_class' => ['nullable', 'string', 'max:100'],
            'order_column' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

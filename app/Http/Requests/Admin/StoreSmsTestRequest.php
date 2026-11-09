<?php

namespace App\Http\Requests\Admin;

use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;

class StoreSmsTestRequest extends FormRequest
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
            'to' => ['required', 'string', 'max:20', new Telephone],
            'body' => ['nullable', 'string', 'max:1600'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'to.required' => 'Indiquez un numéro de destination.',
        ];
    }
}

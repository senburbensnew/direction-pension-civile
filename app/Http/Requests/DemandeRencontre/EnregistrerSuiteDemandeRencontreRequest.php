<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class EnregistrerSuiteDemandeRencontreRequest extends FormRequest
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
            'suite' => [
                'required',
                'string',
                'max:5000',
            ],
        ];
    }
}

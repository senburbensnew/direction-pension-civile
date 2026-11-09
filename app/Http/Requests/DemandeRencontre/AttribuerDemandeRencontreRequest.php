<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class AttribuerDemandeRencontreRequest extends FormRequest
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
            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}

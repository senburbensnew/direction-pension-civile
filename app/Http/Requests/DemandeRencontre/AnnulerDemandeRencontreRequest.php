<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class AnnulerDemandeRencontreRequest extends FormRequest
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
            'motif' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}

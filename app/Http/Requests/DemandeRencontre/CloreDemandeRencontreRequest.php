<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class CloreDemandeRencontreRequest extends FormRequest
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
            'statut' => [
                'required',
                'in:realise,non_honore',
            ],

            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class EnregistrerFormaliteDemandeRencontreRequest extends FormRequest
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
            'annee_fiscale_id' => [
                'required',
                'integer',
                'exists:annees_fiscales,id',
            ],
            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}

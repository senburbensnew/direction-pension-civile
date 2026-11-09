<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class ProposerCreneauDemandeRencontreRequest extends FormRequest
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
            'date_souhaitee' => [
                'required',
                'date',
            ],

            'heure_souhaitee' => [
                'required',
                'date_format:H:i',
            ],

            'agent_id' => [
                'nullable',
                'integer',
            ],

            'lieu_rdv' => [
                'nullable',
                'string',
                'max:255',
            ],

            'report' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}

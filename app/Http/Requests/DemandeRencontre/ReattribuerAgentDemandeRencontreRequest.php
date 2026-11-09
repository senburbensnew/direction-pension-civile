<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class ReattribuerAgentDemandeRencontreRequest extends FormRequest
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
            'agent_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\DemandeRencontre;

use Illuminate\Foundation\Http\FormRequest;

class RefuserDemandeRencontreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'commentaire' => $this->input('commentaire')
                ?: $this->input('motif'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'commentaire' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }
}

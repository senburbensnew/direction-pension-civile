<?php

namespace App\Http\Requests\Demande;

use Illuminate\Foundation\Http\FormRequest;

abstract class DemandeMotifRequest extends FormRequest
{
    abstract protected function motifMax(): int;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motif' => ['nullable', 'string', 'max:'.$this->motifMax()],
        ];
    }
}

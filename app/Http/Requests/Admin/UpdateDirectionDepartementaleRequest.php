<?php

namespace App\Http\Requests\Admin;

use App\Models\DirectionDepartementale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDirectionDepartementaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var DirectionDepartementale $direction */
        $direction = $this->route('direction');

        return [
            'abbr' => ['required', 'string', 'max:20', Rule::unique('direction_departementales', 'abbr')->ignore($direction->id)],
            'nom' => ['required', 'string', 'max:255'],
            'ville' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:3000'],
            'color' => ['required', 'string', 'max:30'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

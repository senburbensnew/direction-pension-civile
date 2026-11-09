<?php

namespace App\Http\Requests;

use App\Enums\PensionMandatTypeEnum;
use App\Rules\CodePension;
use Illuminate\Foundation\Http\FormRequest;

class StorePensionMandatRequest extends FormRequest
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
        $typeValues = array_map(
            fn (PensionMandatTypeEnum $case) => $case->value,
            PensionMandatTypeEnum::cases(),
        );

        return [
            'beneficiaire_firstname' => ['required', 'string', 'max:120'],
            'beneficiaire_lastname' => ['required', 'string', 'max:120'],
            'beneficiaire_nif' => ['nullable', 'string', 'max:32'],
            'code_pension' => ['required', 'string', new CodePension()],
            'type_mandat' => ['required', 'string', 'in:'.implode(',', $typeValues)],
            'type_autre_precision' => [
                'nullable',
                'string',
                'max:120',
                'required_if:type_mandat,autre',
            ],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'mandat_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}

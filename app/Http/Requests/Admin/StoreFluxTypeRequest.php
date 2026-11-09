<?php

namespace App\Http\Requests\Admin;

use App\Enums\TypeDemandeEnum;
use App\Models\TypeDemande;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFluxTypeRequest extends FormRequest
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
        $knownCodes = $this->knownTypeCodes();

        return [
            'type_code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_]+$/',
                'unique:types_demandes,code',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (TypeDemandeEnum::tryFrom($value)) {
                        $fail('Ce code correspond déjà à un type de demande existant.');
                    }
                },
            ],
            'type_label' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'clone_from' => ['nullable', 'string', Rule::in(array_merge($knownCodes, ['__global__']))],
        ];
    }

    /**
     * @return list<string>
     */
    private function knownTypeCodes(): array
    {
        $fromEnum = array_map(
            fn (TypeDemandeEnum $e) => $e->value,
            TypeDemandeEnum::cases(),
        );

        $fromDb = TypeDemande::query()->pluck('code')->all();

        return array_values(array_unique(array_merge($fromEnum, $fromDb)));
    }
}

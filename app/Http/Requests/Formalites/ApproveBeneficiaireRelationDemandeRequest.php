<?php

namespace App\Http\Requests\Formalites;

use App\Enums\BeneficiaireLinkPermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveBeneficiaireRelationDemandeRequest extends FormRequest
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
            'permissions' => ['nullable', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in(array_column(BeneficiaireLinkPermissionEnum::cases(), 'value'))],
        ];
    }
}

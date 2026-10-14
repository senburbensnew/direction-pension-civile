<?php

namespace App\Http\Requests;

use App\Enums\TypeDemandeEnum;
use App\Http\Requests\Concerns\AuthorizesBeneficiaireDemande;
use App\Rules\CodePension;
use App\Rules\Nif;
use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeAttestationRequest extends FormRequest
{
    use AuthorizesBeneficiaireDemande;

    public function authorize(): bool
    {
        return $this->authorizeBeneficiaireDemande(TypeDemandeEnum::DEMANDE_ATTESTATION);
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'action' => 'required|in:draft,submit',
            'demande_id' => 'sometimes|nullable|exists:demandes,id',
            'code_pension' => ['nullable', 'required_if:action,submit', new CodePension],
            'nif' => ['nullable', 'required_if:action,submit', new Nif],
            'prenom' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
            'nom' => ['nullable', 'required_if:action,submit', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code_pension' => 'code du pensionné',
            'nif' => 'NIF',
            'prenom' => 'Prénom',
            'nom' => 'Nom',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
        ];
    }
}

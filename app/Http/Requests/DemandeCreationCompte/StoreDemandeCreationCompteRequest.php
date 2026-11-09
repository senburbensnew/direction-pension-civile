<?php

namespace App\Http\Requests\DemandeCreationCompte;

use App\Http\Requests\DemandeCreationCompte\Concerns\ValidatesDemandeCreationCompteSubmission;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDemandeCreationCompteRequest extends FormRequest
{
    use ValidatesDemandeCreationCompteSubmission;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->baseDemandeCreationCompteRules(requireTerms: true);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->demandeCreationCompteValidationMessages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->demandeCreationCompteValidationAttributes();
    }

    public function expectsDemandeJsonResponse(): bool
    {
        return $this->expectsJson()
            || $this->ajax()
            || $this->wantsJson()
            || $this->filled('idempotency_key');
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsDemandeJsonResponse()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Certains champs sont invalides.',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}

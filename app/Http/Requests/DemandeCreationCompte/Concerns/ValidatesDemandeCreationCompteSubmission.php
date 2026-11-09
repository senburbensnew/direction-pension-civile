<?php

namespace App\Http\Requests\DemandeCreationCompte\Concerns;

use App\Models\AffiliationPension;
use App\Models\DemandeCreationCompte;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Rules\CodePension;
use App\Rules\Nif;
use App\Rules\Ninu;
use App\Rules\PensionnaireReferenceExists;
use App\Rules\Telephone;
use App\Services\Pension\BeneficiaireProvisioningService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** @mixin FormRequest */
trait ValidatesDemandeCreationCompteSubmission
{
    protected function isMandataireSubmission(): bool
    {
        return $this->boolean('is_mandataire');
    }

    protected function isMineurSubmission(): bool
    {
        return $this->boolean('is_mineur') && ! $this->isMandataireSubmission();
    }

    /**
     * @return array<string, mixed>
     */
    protected function baseDemandeCreationCompteRules(bool $requireTerms): array
    {
        $isMandataire = $this->isMandataireSubmission();
        $isMineur = $this->isMineurSubmission();

        $rules = [
            'idempotency_key' => ['nullable', 'string', 'max:64'],
            'user_type' => 'required|in:pensionne,externe',
            'is_mandataire' => ['sometimes', 'boolean'],
            'nif' => array_merge(
                $isMineur ? ['nullable'] : ['required'],
                [
                    'string',
                    new Nif,
                    function (string $attribute, mixed $value, \Closure $fail) {
                        if (blank($value)) {
                            return;
                        }

                        if (
                            User::nifExists($value) ||
                            DemandeCreationCompte::pendingDigitsMatch(
                                'nif',
                                User::normalizeDigits($value)
                            )
                        ) {
                            $fail('Ce NIF est déjà utilisé.');
                        }
                    },
                ]
            ),
            'ninu' => [
                'nullable',
                'string',
                new Ninu,
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        User::ninuExists($value) ||
                        DemandeCreationCompte::pendingDigitsMatch(
                            'ninu',
                            User::normalizeDigits($value)
                        )
                    ) {
                        $fail('Ce NINU est déjà utilisé.');
                    }
                },
            ],
            'pension_code' => array_merge(
                ['nullable'],
                [
                    'string',
                    new CodePension,
                    new PensionnaireReferenceExists,
                    function (string $attribute, mixed $value, \Closure $fail) use ($isMandataire, $isMineur) {
                        if (blank($value)) {
                            return;
                        }

                        if ($isMandataire || $isMineur) {
                            $this->assertPensionCodeIsNotProxyOwnTitulaireCode(
                                (string) $value,
                                $isMineur,
                                $isMandataire,
                                $fail
                            );

                            return;
                        }

                        if (
                            AffiliationPension::codeExists($value) ||
                            DemandeCreationCompte::pendingDigitsMatch(
                                'pension_code',
                                User::normalizeDigits($value)
                            )
                        ) {
                            $fail('Ce code pension est déjà utilisé.');
                        }
                    },
                ]
            ),
            'telephone' => [
                'required',
                'string',
                new Telephone,
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        User::telephoneExists($value) ||
                        DemandeCreationCompte::pendingTelephoneExists($value)
                    ) {
                        $fail('Ce numéro de téléphone est déjà utilisé.');
                    }
                },
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        filled($value) &&
                        is_string($value) &&
                        User::emailExists($value)
                    ) {
                        $fail('Cette adresse e-mail est déjà utilisée.');
                    }
                },
            ],
            'adresse' => [
                'required',
                'filled',
                'string',
                'max:500',
            ],
            'is_mineur' => [
                'sometimes',
                'boolean',
            ],

            'piece_identite_type' => $isMineur
                ? 'nullable'
                : 'required|in:permis,passeport,cin,matricule_fiscal',

            'piece_identite' => $isMineur
                ? 'nullable'
                : $this->identityFileRuleString(),

            'acte_naissance' => $isMineur
                ? $this->identityFileRuleString()
                : 'nullable',

            'representant_lien' => $isMineur
                ? 'required|in:pere,mere,tuteur'
                : 'nullable',

            'representant_est_pensionne' => $isMineur
                ? 'sometimes|boolean'
                : 'nullable',

            'piece_identite_representant_type' => $isMineur
                ? 'required|in:permis,passeport,cin'
                : 'nullable',

            'piece_identite_representant' => $isMineur
                ? $this->identityFileRuleString()
                : 'nullable',

            'mandat_designation' => $isMandataire
                ? $this->identityFileRuleString()
                : 'nullable',

            'mandataire_est_pensionne' => $isMandataire
                ? 'sometimes|boolean'
                : 'nullable',

            'ocr_documents_json' => [
                'nullable',
                'string',
                'max:524288',
            ],
        ];

        if ($requireTerms) {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'max:72',
                Password::defaults(),
            ];

            $rules['accept_terms'] = 'accepted';
        }

        return $rules;
    }

    protected function identityFileRuleString(): string
    {
        return 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';
    }

    /**
     * @return array<string, string>
     */
    protected function demandeCreationCompteValidationMessages(): array
    {
        return [
            'required' => 'Le champ « :attribute » est obligatoire.',
            'accepted' => 'Vous devez accepter les :attribute.',
            'mimes' => 'Le fichier « :attribute » doit être une image (JPG, PNG, WEBP) ou un PDF.',
            'email' => 'Le champ « :attribute » doit être une adresse e-mail valide.',
            'min' => 'Le champ « :attribute » doit contenir au moins :min caractères.',
            'in' => 'La valeur sélectionnée pour « :attribute » est invalide.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function demandeCreationCompteValidationAttributes(): array
    {
        return [
            'user_type' => 'type de compte',
            'nif' => 'matricule fiscal (NIF)',
            'ninu' => 'NINU',
            'pension_code' => 'code pension',
            'telephone' => 'numéro de téléphone',
            'email' => 'adresse e-mail',
            'adresse' => 'adresse actuelle',
            'is_mineur' => 'pensionné mineur',
            'piece_identite_type' => 'type de pièce d’identité',
            'piece_identite' => 'pièce d’identité du pensionné',
            'acte_naissance' => 'acte de naissance du pensionné mineur',
            'representant_lien' => 'lien du représentant',
            'piece_identite_representant_type' => 'type de pièce d’identité du représentant',
            'piece_identite_representant' => 'pièce d’identité du représentant',
            'mandat_designation' => 'formulaire de désignation du mandataire',
            'password' => 'mot de passe',
            'accept_terms' => 'conditions d’utilisation et politique de confidentialité',
        ];
    }

    protected function assertPensionCodeIsNotProxyOwnTitulaireCode(
        string $code,
        bool $isMineur,
        bool $isMandataire,
        \Closure $fail,
    ): void {
        $alsoTitulaire = ($isMineur && $this->boolean('representant_est_pensionne'))
            || ($isMandataire && $this->boolean('mandataire_est_pensionne'));

        if (! $alsoTitulaire) {
            return;
        }

        $ref = PensionnaireReference::query()
            ->whereRaw('UPPER(TRIM(PENSIONNAIRE_ID)) = ?', [strtoupper(trim($code))])
            ->first();

        if (! $ref) {
            return;
        }

        $raw = $this->input('ocr_documents_json');
        $ocrDocuments = [];

        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $ocrDocuments = $decoded;
            }
        }

        $provisioning = app(BeneficiaireProvisioningService::class);
        $matcher = new PensionnaireReferenceExists;

        $proxyKey = $isMineur ? 'piece_identite_representant' : 'piece_identite';
        $proxyFields = $provisioning->fieldsFromDocument($ocrDocuments, $proxyKey);

        if (! $matcher->rowMatchesIdentity(
            $ref,
            $proxyFields['nom'] ?? null,
            $proxyFields['prenom'] ?? null,
        )) {
            return;
        }

        if ($isMineur) {
            $minorFields = $provisioning->fieldsFromDocument($ocrDocuments, 'acte_naissance');
            if ($matcher->rowMatchesIdentity(
                $ref,
                $minorFields['nom'] ?? null,
                $minorFields['prenom'] ?? null,
            )) {
                return;
            }
        }

        $fail(
            'Indiquez le code pension du pensionné concerné (mineur ou personne mandante), '
            .'pas votre code de titulaire.'
        );
    }
}

<?php

namespace App\Services\Pension;

use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use Illuminate\Support\Facades\DB;

class BeneficiaireProvisioningService
{
    public function ensureForUser(User $user): ?Beneficiaire
    {
        if (UserBeneficiaireLink::query()->where('user_id', $user->id)->exists()) {
            return UserBeneficiaireLink::query()
                ->where('user_id', $user->id)
                ->where('relation', BeneficiaireRelationEnum::SELF->value)
                ->first()?->beneficiaire;
        }

        if (! $user->hasCategory('pensionne') && ! $user->hasRole('pensionne')) {
            return null;
        }

        return $this->provisionAdultSelf($user);
    }

    /**
     * @param  array<int, array<string, mixed>>  $ocrDocuments
     */
    public function provisionFromDemandeCreation(
        User $user,
        DemandeCreationCompte $demande,
        array $ocrDocuments,
        bool $isMineur,
        ?User $reviewer = null,
    ): Beneficiaire {
        return DB::transaction(function () use ($user, $demande, $ocrDocuments, $isMineur, $reviewer) {
            if ($isMineur) {
                if ($reviewer) {
                    return $this->finalizeMinorFromDemandeCreation($user, $demande, $ocrDocuments, $reviewer);
                }

                return $this->provisionMinor($user, $demande, $ocrDocuments, null);
            }

            return $this->provisionAdultSelf($user, $demande, null, $reviewer);
        });
    }

    public function provisionAdultSelf(
        User $user,
        ?DemandeCreationCompte $demande = null,
        ?string $pensionCode = null,
        ?User $reviewer = null,
    ): Beneficiaire {
        $code = $pensionCode ?? $demande?->pension_code;

        $existing = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first();

        if ($existing) {
            $beneficiaire = $existing->beneficiaire;
            $beneficiaire
                ->mergeInformationsComplementaires($this->buildInformationsComplementaires($user, $demande))
                ->save();
            $this->syncAffiliation($beneficiaire, $code);

            return $beneficiaire;
        }

        $complementaires = $this->buildInformationsComplementaires($user, $demande);

        $beneficiaire = Beneficiaire::create([
            'firstname' => $user->firstname ?? $demande?->firstname,
            'lastname' => $user->lastname ?? $demande?->lastname,
            'nif' => $complementaires['nif'] ?? $user->nif ?? $demande?->nif,
            'is_mineur' => false,
            'informations_complementaires' => $complementaires,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::SELF,
            'status' => BeneficiaireLinkStatusEnum::ACTIVE,
            'verifie_par' => $reviewer?->id,
            'verifie_le' => $reviewer ? now() : null,
        ]);

        $this->syncAffiliation($beneficiaire, $code);

        return $beneficiaire;
    }

    /**
     * @param  array<int, array<string, mixed>>  $ocrDocuments
     */
    private function provisionMinor(
        User $user,
        DemandeCreationCompte $demande,
        array $ocrDocuments,
        ?User $reviewer,
    ): Beneficiaire {
        $existing = $this->findMinorRepresentativeContext($user, $demande);

        if ($existing) {
            return $existing['beneficiaire'];
        }

        $minorFields = $this->fieldsFromDocument($ocrDocuments, 'acte_naissance');
        $relation = BeneficiaireRelationEnum::tryFrom((string) ($demande->representant_lien ?? ''))
            ?? BeneficiaireRelationEnum::TUTEUR;

        $complementaires = $this->buildInformationsComplementaires($user, $demande, $ocrDocuments, $minorFields);

        $beneficiaire = Beneficiaire::create([
            'firstname' => $minorFields['prenom'] ?? $demande->firstname,
            'lastname' => $minorFields['nom'] ?? $demande->lastname,
            'nif' => $complementaires['nif'] ?? $demande->nif,
            'is_mineur' => true,
            'informations_complementaires' => $complementaires,
        ]);

        $link = UserBeneficiaireLink::create([
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => $relation,
            'status' => BeneficiaireLinkStatusEnum::EN_ATTENTE,
            'permissions' => BeneficiaireLinkPermissionEnum::defaultGrant(),
            'demande_par' => $user->id,
            'verifie_par' => null,
            'verifie_le' => null,
        ]);

        $this->syncAffiliation($beneficiaire, $demande->pension_code, true);

        app(BeneficiaireRelationService::class)->registerFromDemandeCreationCompte(
            $demande,
            $user,
            $beneficiaire,
            $relation,
            $link,
        );

        return $beneficiaire;
    }

    private function finalizeMinorFromDemandeCreation(
        User $user,
        DemandeCreationCompte $demande,
        array $ocrDocuments,
        User $reviewer,
    ): Beneficiaire {
        $existing = $this->findMinorRepresentativeContext($user, $demande);

        if ($existing) {
            $link = $existing['link'];
            $link->update([
                'status' => BeneficiaireLinkStatusEnum::ACTIVE,
                'verifie_par' => $reviewer->id,
                'verifie_le' => now(),
                'motif_decision' => 'Acceptation demande de création de compte '.$demande->code,
            ]);

            app(BeneficiaireRelationService::class)->finalizeFromDemandeCreationCompte($demande, $reviewer);

            return $existing['beneficiaire'];
        }

        $beneficiaire = $this->provisionMinor($user, $demande, $ocrDocuments, null);
        $link = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();

        $link?->update([
            'status' => BeneficiaireLinkStatusEnum::ACTIVE,
            'verifie_par' => $reviewer->id,
            'verifie_le' => now(),
            'motif_decision' => 'Acceptation demande de création de compte '.$demande->code,
        ]);

        app(BeneficiaireRelationService::class)->finalizeFromDemandeCreationCompte($demande, $reviewer);

        return $beneficiaire;
    }

    /**
     * @return array{beneficiaire: Beneficiaire, link: UserBeneficiaireLink}|null
     */
    private function findMinorRepresentativeContext(User $user, DemandeCreationCompte $demande): ?array
    {
        $relationDemande = \App\Models\BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demande->id)
            ->with(['beneficiaire', 'link'])
            ->first();

        if ($relationDemande?->beneficiaire) {
            $link = UserBeneficiaireLink::query()
                ->where('user_id', $user->id)
                ->where('beneficiaire_id', $relationDemande->beneficiaire_id)
                ->first();

            if ($link) {
                return ['beneficiaire' => $relationDemande->beneficiaire, 'link' => $link];
            }
        }

        $code = AffiliationPension::normalizeCode((string) ($demande->pension_code ?? ''));
        $affiliation = $code !== '' ? AffiliationPension::findByCode($code) : null;
        $beneficiaire = $affiliation?->beneficiaire;

        if (! $beneficiaire || ! $beneficiaire->is_mineur) {
            return null;
        }

        $link = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->whereIn('relation', [
                BeneficiaireRelationEnum::PERE->value,
                BeneficiaireRelationEnum::MERE->value,
                BeneficiaireRelationEnum::TUTEUR->value,
            ])
            ->first();

        if (! $link) {
            return null;
        }

        return ['beneficiaire' => $beneficiaire, 'link' => $link];
    }

    public function syncAffiliation(Beneficiaire $beneficiaire, ?string $code, bool $primary = true): ?AffiliationPension
    {
        if (! filled($code)) {
            return null;
        }

        $code = AffiliationPension::normalizeCode($code);
        $existing = AffiliationPension::findByCode($code);

        if ($existing && $existing->beneficiaire_id !== $beneficiaire->id) {
            return $existing;
        }

        $affiliation = AffiliationPension::query()->firstOrCreate(
            ['code_pension' => $code],
            [
                'beneficiaire_id' => $beneficiaire->id,
                'is_primary' => $primary,
            ]
        );

        if ($primary) {
            AffiliationPension::query()
                ->where('beneficiaire_id', $beneficiaire->id)
                ->where('id', '!=', $affiliation->id)
                ->update(['is_primary' => false]);

            $affiliation->update(['is_primary' => true, 'beneficiaire_id' => $beneficiaire->id]);
        }

        return $affiliation;
    }

    /**
     * @param  array<int, array<string, mixed>>  $ocrDocuments
     * @param  array<string, string>  $extraDocumentFields
     * @return array<string, string>
     */
    private function buildInformationsComplementaires(
        User $user,
        ?DemandeCreationCompte $demande = null,
        array $ocrDocuments = [],
        array $extraDocumentFields = [],
    ): array {
        if ($ocrDocuments === [] && $demande && is_array($demande->ocr_documents)) {
            $ocrDocuments = $demande->ocr_documents;
        }

        $cinFields = $this->fieldsFromDocument($ocrDocuments, 'identite_cin');
        $documentFields = array_merge($cinFields, $extraDocumentFields);

        $raw = [
            'nif' => $user->nif ?? $demande?->nif ?? $documentFields['nif'] ?? null,
            'ninu' => $user->ninu ?? $demande?->ninu
                ?? $documentFields['ninu']
                ?? $documentFields['numero_identification_unique']
                ?? null,
            'cin' => $documentFields['cin'] ?? $documentFields['numero_cin'] ?? null,
            'telephone' => $demande?->telephone ?? $user->phone,
            'email' => $demande?->email ?? $user->email,
            'adresse' => $documentFields['adresse'] ?? null,
            'sexe' => $documentFields['sexe'] ?? null,
            'lieu_naissance' => $documentFields['lieu_naissance'] ?? null,
            'nationalite' => $documentFields['nationalite'] ?? null,
        ];

        return Beneficiaire::sanitizeInformationsComplementaires($raw);
    }

    /**
     * @param  array<int, array<string, mixed>>  $documents
     * @return array<string, string>
     */
    public function fieldsFromDocument(array $documents, string $key): array
    {
        foreach ($documents as $doc) {
            if (($doc['key'] ?? '') !== $key) {
                continue;
            }

            $flat = [];

            foreach (($doc['fields'] ?? []) as $k => $v) {
                if ($k === 'type_document') {
                    continue;
                }

                $val = trim((string) $v);

                if ($val !== '') {
                    $flat[$k] = $val;
                }
            }

            return $flat;
        }

        return [];
    }

    public function beneficiaireForPensionCode(string $code): Beneficiaire
    {
        $code = AffiliationPension::normalizeCode($code);
        $affiliation = AffiliationPension::findByCode($code);

        if ($affiliation?->beneficiaire) {
            return $affiliation->beneficiaire;
        }

        $reference = PensionnaireReference::query()
            ->whereRaw('UPPER(TRIM(PENSIONNAIRE_ID)) = ?', [$code])
            ->first();

        $complementaires = Beneficiaire::sanitizeInformationsComplementaires([
            'nif' => $reference?->NIF,
            'sexe' => $reference?->SEXE,
        ]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => $reference?->PRENOM,
            'lastname' => $reference?->NOM,
            'nif' => $complementaires['nif'] ?? null,
            'is_mineur' => false,
            'informations_complementaires' => $complementaires !== [] ? $complementaires : null,
        ]);

        $this->syncAffiliation($beneficiaire, $code, true);

        return $beneficiaire;
    }

    public function provisionMandataireFromDemandeCreation(
        User $user,
        DemandeCreationCompte $demande,
    ): PensionMandat {
        return app(PensionMandatService::class)->registerFromDemandeCreationCompte($user, $demande);
    }
}

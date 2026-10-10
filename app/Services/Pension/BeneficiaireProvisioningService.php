<?php

namespace App\Services\Pension;

use App\Enums\BeneficiaireRelationEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\DemandeCreationCompte;
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
        bool $isMineur
    ): Beneficiaire {
        return DB::transaction(function () use ($user, $demande, $ocrDocuments, $isMineur) {
            if ($isMineur) {
                return $this->provisionMinor($user, $demande, $ocrDocuments);
            }

            return $this->provisionAdultSelf($user, $demande);
        });
    }

    public function provisionAdultSelf(User $user, ?DemandeCreationCompte $demande = null, ?string $pensionCode = null): Beneficiaire
    {
        $code = $pensionCode ?? $demande?->pension_code;

        $existing = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first();

        if ($existing) {
            $beneficiaire = $existing->beneficiaire;
            $this->syncAffiliation($beneficiaire, $code);

            return $beneficiaire;
        }

        $beneficiaire = Beneficiaire::create([
            'firstname' => $user->firstname ?? $demande?->firstname,
            'lastname' => $user->lastname ?? $demande?->lastname,
            'nif' => $user->nif ?? $demande?->nif,
            'is_mineur' => false,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::SELF,
        ]);

        $this->syncAffiliation($beneficiaire, $code);

        return $beneficiaire;
    }

    /**
     * @param  array<int, array<string, mixed>>  $ocrDocuments
     */
    private function provisionMinor(User $user, DemandeCreationCompte $demande, array $ocrDocuments): Beneficiaire
    {
        $minorFields = $this->fieldsFromDocument($ocrDocuments, 'acte_naissance');
        $relation = BeneficiaireRelationEnum::tryFrom((string) ($demande->representant_lien ?? ''))
            ?? BeneficiaireRelationEnum::TUTEUR;

        $beneficiaire = Beneficiaire::create([
            'firstname' => $minorFields['prenom'] ?? $demande->firstname,
            'lastname' => $minorFields['nom'] ?? $demande->lastname,
            'nif' => $demande->nif,
            'is_mineur' => true,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => $relation,
        ]);

        $this->syncAffiliation($beneficiaire, $demande->pension_code, true);

        return $beneficiaire;
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
}

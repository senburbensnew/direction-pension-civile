<?php

namespace App\Services\Pension;

use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Models\BeneficiaireRelationDemande;
use App\Models\BeneficiaireRelationEvent;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BeneficiaireRelationService
{
    public function __construct(
        private readonly BeneficiaireProvisioningService $provisioning,
    ) {}

    /**
     * @param  list<string>|null  $permissions
     */
    public function submitDemande(
        User $user,
        string $codePension,
        BeneficiaireRelationEnum $typeRelation,
        ?string $motif = null,
        ?UploadedFile $justificatif = null,
        ?array $permissions = null,
    ): BeneficiaireRelationDemande {
        if (in_array($typeRelation, [BeneficiaireRelationEnum::SELF, BeneficiaireRelationEnum::MANDATAIRE], true)) {
            throw new \InvalidArgumentException('Type de relation non autorisé pour cette demande.');
        }

        $beneficiaire = $this->provisioning->beneficiaireForPensionCode($codePension);

        return DB::transaction(function () use ($user, $beneficiaire, $typeRelation, $motif, $justificatif, $permissions) {
            $demande = BeneficiaireRelationDemande::create([
                'user_id' => $user->id,
                'beneficiaire_id' => $beneficiaire->id,
                'type_relation' => $typeRelation,
                'status' => BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE,
                'motif' => $motif,
                'permissions' => $permissions,
            ]);

            if ($justificatif) {
                $demande->addMedia($justificatif)->toMediaCollection('justificatif');
            }

            BeneficiaireRelationEvent::record(
                $demande,
                'demande_creee',
                $user,
                null,
                $demande->only(['status', 'type_relation', 'beneficiaire_id']),
                $motif,
            );

            return $demande;
        });
    }

    /**
     * @param  list<string>|null  $permissions
     */
    public function approve(BeneficiaireRelationDemande $demande, User $reviewer, ?array $permissions = null): UserBeneficiaireLink
    {
        return DB::transaction(function () use ($demande, $reviewer, $permissions) {
            $resolvedPermissions = filled($permissions)
                ? $permissions
                : (filled($demande->permissions)
                    ? $demande->permissions
                    : BeneficiaireLinkPermissionEnum::defaultGrant());

            $link = UserBeneficiaireLink::query()->updateOrCreate(
                [
                    'user_id' => $demande->user_id,
                    'beneficiaire_id' => $demande->beneficiaire_id,
                    'relation' => $demande->type_relation->value,
                ],
                [
                    'status' => BeneficiaireLinkStatusEnum::ACTIVE,
                    'permissions' => $resolvedPermissions,
                    'motif' => $demande->motif,
                    'demande_par' => $demande->user_id,
                    'verifie_par' => $reviewer->id,
                    'verifie_le' => now(),
                    'valid_from' => $demande->valid_from,
                    'valid_until' => $demande->valid_until,
                ]
            );

            $demande->update([
                'status' => BeneficiaireRelationDemandeStatusEnum::ACCEPTEE,
                'validated_by' => $reviewer->id,
                'validated_at' => now(),
                'refusal_reason' => null,
                'user_beneficiaire_link_id' => $link->id,
            ]);

            BeneficiaireRelationEvent::record(
                $link,
                'relation_approuvee',
                $reviewer,
                null,
                $link->fresh()->toArray(),
                'Demande #'.$demande->id,
            );

            return $link;
        });
    }

    public function refuse(BeneficiaireRelationDemande $demande, User $reviewer, string $reason): void
    {
        DB::transaction(function () use ($demande, $reviewer, $reason) {
            $before = $demande->only(['status']);
            $demande->update([
                'status' => BeneficiaireRelationDemandeStatusEnum::REFUSEE,
                'refusal_reason' => $reason,
                'validated_by' => $reviewer->id,
                'validated_at' => now(),
            ]);

            BeneficiaireRelationEvent::record(
                $demande,
                'relation_refusee',
                $reviewer,
                $before,
                $demande->only(['status', 'refusal_reason']),
                $reason,
            );
        });
    }

    public function revokeLink(UserBeneficiaireLink $link, User $acteur, ?string $commentaire = null): void
    {
        DB::transaction(function () use ($link, $acteur, $commentaire) {
            $before = $link->only(['status', 'valid_until']);
            $link->update([
                'status' => BeneficiaireLinkStatusEnum::REVOQUE,
                'valid_until' => now()->toDateString(),
            ]);

            BeneficiaireRelationEvent::record(
                $link,
                'relation_revoquee',
                $acteur,
                $before,
                $link->only(['status', 'valid_until']),
                $commentaire,
            );
        });
    }

    public function expireDueLinks(): int
    {
        $count = 0;
        $today = now()->toDateString();

        UserBeneficiaireLink::query()
            ->where('status', BeneficiaireLinkStatusEnum::ACTIVE->value)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', $today)
            ->each(function (UserBeneficiaireLink $link) use (&$count) {
                $before = $link->only(['status']);
                $link->update(['status' => BeneficiaireLinkStatusEnum::EXPIRE]);
                BeneficiaireRelationEvent::record(
                    $link,
                    'relation_expiree',
                    null,
                    $before,
                    $link->only(['status']),
                );
                $count++;
            });

        return $count;
    }
}

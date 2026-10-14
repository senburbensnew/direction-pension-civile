<?php

namespace App\Models;

use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBeneficiaireLink extends Model
{
    protected $fillable = [
        'user_id',
        'beneficiaire_id',
        'relation',
        'status',
        'permissions',
        'motif',
        'demande_par',
        'verifie_par',
        'verifie_le',
        'motif_decision',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'verifie_le' => 'datetime',
        'relation' => BeneficiaireRelationEnum::class,
        'status' => BeneficiaireLinkStatusEnum::class,
        'permissions' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function beneficiaire(): BelongsTo
    {
        return $this->belongsTo(Beneficiaire::class);
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demande_par');
    }

    public function verificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifie_par');
    }

    public function resolvedStatus(): BeneficiaireLinkStatusEnum
    {
        $status = $this->status;

        if ($status instanceof BeneficiaireLinkStatusEnum) {
            return $status;
        }

        return BeneficiaireLinkStatusEnum::tryFrom((string) $status)
            ?? BeneficiaireLinkStatusEnum::ACTIVE;
    }

    public function isWithinValidityPeriod(): bool
    {
        $today = now()->startOfDay();

        if ($this->valid_from && $this->valid_from->gt($today)) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->lt($today)) {
            return false;
        }

        return true;
    }

    public function isActive(): bool
    {
        return $this->isWithinValidityPeriod() && $this->resolvedStatus()->grantsAccess();
    }

    public function autorise(string $permission): bool
    {
        $permissions = $this->permissions;

        if ($permissions === null || $permissions === []) {
            return in_array($permission, BeneficiaireLinkPermissionEnum::defaultGrant(), true);
        }

        return in_array($permission, $permissions, true);
    }

    /**
     * @return list<string>
     */
    public function grantedPermissionLabels(): array
    {
        $codes = ($this->permissions === null || $this->permissions === [])
            ? BeneficiaireLinkPermissionEnum::defaultGrant()
            : $this->permissions;

        return array_map(
            fn (string $code) => BeneficiaireLinkPermissionEnum::tryFrom($code)?->label() ?? $code,
            $codes,
        );
    }
}

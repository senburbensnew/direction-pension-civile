<?php

namespace App\Models;

use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BeneficiaireRelationDemande extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'beneficiaire_id',
        'type_relation',
        'status',
        'motif',
        'valid_from',
        'valid_until',
        'permissions',
        'refusal_reason',
        'validated_by',
        'validated_at',
        'user_beneficiaire_link_id',
    ];

    protected $casts = [
        'status' => BeneficiaireRelationDemandeStatusEnum::class,
        'type_relation' => BeneficiaireRelationEnum::class,
        'valid_from' => 'date',
        'valid_until' => 'date',
        'permissions' => 'array',
        'validated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function beneficiaire(): BelongsTo
    {
        return $this->belongsTo(Beneficiaire::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(UserBeneficiaireLink::class, 'user_beneficiaire_link_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('justificatif')
            ->singleFile()
            ->useDisk(config('uploads.disks.private', 'private'));
    }
}

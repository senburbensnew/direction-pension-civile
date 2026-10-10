<?php

namespace App\Models;

use App\Enums\BeneficiaireRelationEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBeneficiaireLink extends Model
{
    protected $fillable = [
        'user_id',
        'beneficiaire_id',
        'relation',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'relation' => BeneficiaireRelationEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function beneficiaire(): BelongsTo
    {
        return $this->belongsTo(Beneficiaire::class);
    }

    public function isActive(): bool
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
}

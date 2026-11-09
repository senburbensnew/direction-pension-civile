<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Institution extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

    protected $fillable = [
        'raison_sociale',
        'sigle',
        'nif',
        'code',
        'statut',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function loginUser(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function isActive(): bool
    {
        return $this->statut === 'actif';
    }

    public function displayName(): string
    {
        return filled($this->sigle)
            ? (string) $this->sigle
            : (string) $this->raison_sociale;
    }
}

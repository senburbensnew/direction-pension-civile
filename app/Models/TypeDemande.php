<?php

namespace App\Models;

use App\Enums\TypeDemandeEnum;
use Illuminate\Database\Eloquent\Model;

class TypeDemande extends Model
{
    protected $table = 'types_demandes';

    protected $fillable = [
        'code',
        'label',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static function labelFor(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        $fromDb = static::query()->where('code', $code)->value('label');
        if ($fromDb) {
            return $fromDb;
        }

        return TypeDemandeEnum::tryFrom($code)?->label() ?? $code;
    }

    public static function isKnown(string $code): bool
    {
        return TypeDemandeEnum::tryFrom($code) !== null
            || static::query()->where('code', $code)->exists();
    }

    public static function isCustom(string $code): bool
    {
        return TypeDemandeEnum::tryFrom($code) === null
            && static::query()->where('code', $code)->exists();
    }
}

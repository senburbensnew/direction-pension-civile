<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliationPension extends Model
{
    protected $fillable = [
        'beneficiaire_id',
        'code_pension',
        'is_primary',
        'loc_description',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function beneficiaire(): BelongsTo
    {
        return $this->belongsTo(Beneficiaire::class);
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($code)) ?? '');
    }

    public static function codeExists(string $code): bool
    {
        return self::findByCode($code) !== null;
    }

    public static function findByCode(string $code): ?self
    {
        $compact = self::normalizeCode($code);
        $digits = User::normalizeDigits($code);

        if ($compact === '') {
            return null;
        }

        return static::query()
            ->where(function ($query) use ($compact, $digits) {
                $query->whereRaw("UPPER(REPLACE(COALESCE(code_pension, ''), ' ', '')) = ?", [$compact]);
                if ($digits !== '') {
                    $query->orWhereRaw(User::digitsComparisonSql('code_pension').' = ?', [$digits]);
                }
            })
            ->first();
    }
}

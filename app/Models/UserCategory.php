<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCategory extends Model
{
    /** @return list<string> */
    public static function configuredCodes(): array
    {
        return array_keys(config('account_categories.categories', []));
    }

    public static function isConfiguredCode(string $code): bool
    {
        return array_key_exists($code, config('account_categories.categories', []));
    }

    /**
     * Catégories métier (référentiel config), pour formulaires admin.
     */
    public static function orderedForAdmin(): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->whereIn('code', static::configuredCodes())
            ->orderBy('label')
            ->get();
    }

    public $incrementing = false;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'label',
        'description',
        'is_system',
        'capabilities',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'capabilities' => 'array',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(UserCategoryAssignment::class, 'user_category_code', 'code');
    }

    /**
     * @return list<string>
     */
    public function defaultRoles(): array
    {
        $fromConfig = config("account_categories.categories.{$this->code}.default_roles", []);

        return is_array($fromConfig) ? array_values($fromConfig) : [];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCategory extends Model
{
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

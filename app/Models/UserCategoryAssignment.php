<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCategoryAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'user_category_code',
        'is_primary',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(UserCategory::class, 'user_category_code', 'code');
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

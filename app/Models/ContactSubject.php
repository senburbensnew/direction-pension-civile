<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContactSubject extends Model
{
    protected $fillable = [
        'slug',
        'label',
        'position',
        'is_active',
        'allows_custom',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allows_custom' => 'boolean',
        'position' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public static function uniqueSlug(string $label, ?int $ignoreId = null): string
    {
        $base = Str::slug($label) ?: 'sujet';
        $slug = $base;
        $i = 1;

        while (static::query()
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'country',
        'is_recurring',
        'is_official',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_official' => 'boolean',
    ];

    public function scopeHaiti(Builder $query): Builder
    {
        return $query->where('country', 'HT');
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('date', $year);
    }
}
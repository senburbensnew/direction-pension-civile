<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Holiday extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

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
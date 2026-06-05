<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlossaireTerm extends Model
{
    protected $fillable = ['term', 'definition', 'category', 'icon', 'order_column', 'published'];

    protected $casts = ['published' => 'boolean'];

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('term');
    }
}

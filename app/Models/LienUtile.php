<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LienUtile extends Model
{
    protected $table = 'liens_utiles';

    protected $fillable = ['name', 'abbr', 'url', 'category', 'order_column', 'published'];

    protected $casts = ['published' => 'boolean'];

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('name');
    }
}

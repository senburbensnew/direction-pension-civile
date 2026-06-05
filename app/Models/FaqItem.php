<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    protected $fillable = ['question', 'answer', 'category', 'order_column', 'published'];

    protected $casts = ['published' => 'boolean'];

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('id');
    }
}

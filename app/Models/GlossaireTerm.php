<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class GlossaireTerm extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

    protected $fillable = ['term', 'definition', 'category', 'icon', 'order_column', 'published'];

    protected $casts = ['published' => 'boolean'];

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('term');
    }
}

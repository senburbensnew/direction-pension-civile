<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InstitutionImage extends Model
{
    protected $fillable = ['title', 'image', 'order', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function imageUrl(): string
    {
        if (str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }
        return Storage::url($this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }
}

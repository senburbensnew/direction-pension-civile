<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Partenaire extends Model
{
    protected $fillable = ['name', 'logo', 'website_url', 'order', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function logoUrl(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        if (str_starts_with($this->logo, 'images/')) {
            return asset($this->logo);
        }
        return Storage::url($this->logo);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}

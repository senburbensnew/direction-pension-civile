<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Model;

class InstitutionImage extends Model
{
    protected $fillable = ['title', 'image', 'order', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function imageUrl(): string
    {
        return PublicMedia::preferredUrl($this->mediaSources());
    }

    /**
     * @return array{url: string, webp: ?string, width: ?int, height: ?int}
     */
    public function mediaSources(): array
    {
        return PublicMedia::forUploadedOrStatic($this->image);
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

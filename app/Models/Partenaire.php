<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    protected $fillable = ['name', 'logo', 'website_url', 'order', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return $this->logoSources()['url'];
    }

    public function logoComponentSrc(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return PublicMedia::componentSrc($this->logo);
    }

    /**
     * @return array{url: string, webp: ?string, width: ?int, height: ?int}|null
     */
    public function logoSources(): ?array
    {
        if (! $this->logo) {
            return null;
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return ['url' => $this->logo, 'webp' => null, 'width' => null, 'height' => null];
        }

        return PublicMedia::forUploadedOrStatic($this->logo);
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

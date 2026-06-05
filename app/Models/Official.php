<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Official extends Model
{
    protected $fillable = [
        'slug', 'role', 'nom', 'sexe', 'photo',
        'biographie', 'discours', 'citation', 'order', 'active',
    ];

    protected $casts = ['active' => 'boolean'];

    public function photoUrl(): string
    {
        if (!$this->photo) {
            $name = urlencode($this->nom);
            $bg   = $this->sexe === 'F' ? 'E91E63' : '0D8ABC';
            return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=fff&size=200";
        }
        if (str_starts_with($this->photo, 'images/') || str_starts_with($this->photo, 'http')) {
            return str_starts_with($this->photo, 'http') ? $this->photo : asset($this->photo);
        }
        return Storage::url($this->photo);
    }

    public function hasDiscours(): bool
    {
        return !empty($this->discours);
    }

    public function hasBiographie(): bool
    {
        return !empty($this->biographie);
    }

    public function biographieHtml(): string
    {
        return $this->toHtml($this->biographie ?? '');
    }

    public function discoursHtml(): string
    {
        return $this->toHtml($this->discours ?? '');
    }

    private function toHtml(string $content): string
    {
        if (empty(trim($content))) {
            return '';
        }
        // Already HTML (saved by Quill) — return as-is
        if ($content !== strip_tags($content)) {
            return $content;
        }
        // Plain text — convert paragraph breaks and line breaks to HTML
        $paragraphs = preg_split('/\n{2,}/', trim($content));
        return implode('', array_map(
            fn($p) => '<p>' . nl2br(e(trim($p))) . '</p>',
            array_filter($paragraphs, fn($p) => trim($p) !== '')
        ));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}

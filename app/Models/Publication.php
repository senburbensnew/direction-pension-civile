<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Publication extends Model
{
    protected $fillable = ['title', 'description', 'type', 'file_path', 'url', 'order_column', 'published'];

    protected $casts = ['published' => 'boolean'];

    /** @deprecated Use PublicationType::options() — kept for backward compatibility */
    public static function types(): array
    {
        return PublicationType::options();
    }

    public function publicationType()
    {
        return $this->belongsTo(PublicationType::class, 'type', 'code');
    }

    public function typeLabel(): string
    {
        return PublicationType::options()[$this->type]
            ?? $this->publicationType?->label
            ?? $this->type;
    }

    public function fileUrl(): ?string
    {
        if (!$this->file_path) return null;
        if (str_starts_with($this->file_path, 'documents/')) {
            return asset($this->file_path);
        }
        return Storage::disk('public')->url($this->file_path);
    }

    public function fileExists(): bool
    {
        if (!$this->file_path) return false;
        if (str_starts_with($this->file_path, 'documents/')) {
            return file_exists(public_path($this->file_path));
        }
        return Storage::disk('public')->exists($this->file_path);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('created_at', 'desc');
    }
}

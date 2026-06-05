<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Publication extends Model
{
    protected $fillable = ['title', 'description', 'type', 'file_path', 'url', 'order_column', 'published'];

    protected $casts = ['published' => 'boolean'];

    public static array $types = [
        'loi'         => 'Loi',
        'decret'      => 'Décret',
        'circulaire'  => 'Circulaire',
        'document'    => 'Document',
        'texte'       => 'Texte officiel',
        'autre'       => 'Autre',
    ];

    public function typeLabel(): string
    {
        return self::$types[$this->type] ?? $this->type;
    }

    public function fileUrl(): ?string
    {
        if (!$this->file_path) return null;
        // files in public/documents/ are served directly; storage disk files via /storage/
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

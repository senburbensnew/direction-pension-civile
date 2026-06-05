<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediathequeItem extends Model
{
    protected $fillable = ['title', 'description', 'type', 'file_path', 'url', 'order_column', 'published'];

    protected $casts = ['published' => 'boolean'];

    public static array $types = [
        'image'    => 'Image',
        'video'    => 'Vidéo',
        'audio'    => 'Audio',
        'document' => 'Document',
    ];

    public function typeLabel(): string
    {
        return self::$types[$this->type] ?? $this->type;
    }

    public function fileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }
        return Storage::url($this->file_path);
    }

    public function isExternal(): bool
    {
        return !$this->file_path && $this->url;
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

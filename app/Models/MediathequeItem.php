<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediathequeItem extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'file_path', 'url',
        'order_column', 'published', 'is_featured',
    ];

    protected $casts = [
        'published'   => 'boolean',
        'is_featured' => 'boolean',
    ];

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
        // Root-relative URLs work regardless of APP_URL host/port (e.g. :8000).
        if (str_starts_with($this->file_path, 'media/')) {
            return '/' . ltrim($this->file_path, '/');
        }
        return '/storage/' . ltrim($this->file_path, '/');
    }

    public function isLegacyPublicFile(): bool
    {
        return $this->file_path && str_starts_with($this->file_path, 'media/');
    }

    public function isExternal(): bool
    {
        return !$this->file_path && $this->url;
    }

    /** YouTube / Vimeo embed URL, or null if not embeddable. */
    public function embedUrl(): ?string
    {
        $url = $this->url;
        if (!$url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        if (str_contains($url, 'youtube.com/embed/') || str_contains($url, 'player.vimeo.com/video/')) {
            return $url;
        }

        return null;
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('created_at', 'desc');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}

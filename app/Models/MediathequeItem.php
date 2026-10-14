<?php

namespace App\Models;

use App\Support\PublicMedia;
use App\Support\Storage\UploadPaths;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediathequeItem extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'file_path', 'url',
        'order_column', 'published', 'is_featured',
    ];

    protected $casts = [
        'published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public static array $types = [
        'image' => 'Image',
        'video' => 'Vidéo',
        'audio' => 'Audio',
        'document' => 'Document',
    ];

    public function typeLabel(): string
    {
        return self::$types[$this->type] ?? $this->type;
    }

    public function fileUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }

        $path = $this->normalizedFilePath();

        if (str_starts_with($path, 'media/')) {
            $publicFile = public_path($path);
            if (is_file($publicFile)) {
                return '/'.ltrim($path, '/');
            }
        }

        if (str_starts_with($path, 'images/') && is_file(public_path($path))) {
            return asset($path);
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return asset('storage/'.$path);
        }

        $webp = PublicMedia::webpSiblingPath($path);
        if ($disk->exists($webp)) {
            return asset('storage/'.$webp);
        }

        return null;
    }

    public function fileComponentSrc(): ?string
    {
        if (! $this->file_path || $this->type !== 'image') {
            return null;
        }

        if (str_starts_with($this->file_path, 'media/')) {
            return $this->file_path;
        }

        $path = $this->normalizedFilePath();

        if (str_starts_with($path, 'images/')) {
            return $path;
        }

        return PublicMedia::componentSrc($path);
    }

    public function isLegacyPublicFile(): bool
    {
        if (! $this->file_path) {
            return false;
        }

        $path = $this->normalizedFilePath();

        return str_starts_with($path, 'media/')
            || str_starts_with($path, 'images/');
    }

    /** @return non-empty-string */
    private function normalizedFilePath(): string
    {
        $path = ltrim(str_replace('\\', '/', $this->file_path), '/');

        if (str_starts_with($path, 'cms/')) {
            return $path;
        }

        if (str_starts_with($path, 'mediatheque/')) {
            return 'cms/'.$path;
        }

        if (str_starts_with($path, 'media/audios/')) {
            return UploadPaths::mediatheque('audio').'/'.basename($path);
        }

        return $path;
    }

    public function isExternal(): bool
    {
        return ! $this->file_path && $this->url;
    }

    /** YouTube / Vimeo embed URL, or null if not embeddable. */
    public function embedUrl(): ?string
    {
        $url = $this->url;
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
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

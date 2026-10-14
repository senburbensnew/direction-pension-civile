<?php

namespace App\Models;

use App\Support\Storage\CmsPathResolver;
use App\Support\Storage\UploadPaths;
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

    public function storageRelativePath(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        $resolved = CmsPathResolver::legacyPathToStorage($this->file_path);

        if ($resolved === null || str_starts_with($resolved, 'http')) {
            return null;
        }

        return $resolved;
    }

    public function fileUrl(): ?string
    {
        $path = $this->storageRelativePath();

        if (! $path) {
            return null;
        }

        $disk = Storage::disk(UploadPaths::publicDisk());

        if ($disk->exists($path)) {
            return asset('storage/'.$path);
        }

        return null;
    }

    public function fileExists(): bool
    {
        $path = $this->storageRelativePath();

        if (! $path) {
            return false;
        }

        return Storage::disk(UploadPaths::publicDisk())->exists($path);
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

<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualiteImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'actualite_id',
        'image_path',
    ];

    /**
     * Get the actualite that owns this image.
     */
    public function actualite()
    {
        return $this->belongsTo(Actualite::class);
    }

    public function url(): string
    {
        return $this->mediaSources()['url'];
    }

    public function componentSrc(): string
    {
        return PublicMedia::componentSrc($this->image_path);
    }

    /**
     * @return array{url: string, webp: ?string, width: ?int, height: ?int}
     */
    public function mediaSources(): array
    {
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return ['url' => $this->image_path, 'webp' => null, 'width' => null, 'height' => null];
        }

        return PublicMedia::forUploadedOrStatic($this->image_path);
    }
}

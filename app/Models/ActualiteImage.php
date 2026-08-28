<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        $path = $this->image_path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::url($path);
    }
}

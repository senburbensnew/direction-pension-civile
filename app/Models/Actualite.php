<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actualite extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content_text',
        'category',
        'posted_in',
        'published', // ✅ replaced published_at
    ];

    protected $casts = [
        'published' => 'boolean', // ✅ ensures true/false
    ];

    /**
     * Get all images associated with the actualite.
     */
    public function images()
    {
        return $this->hasMany(ActualiteImage::class);
    }

    public function coverUrl(): string
    {
        $image = $this->images->first();

        return $image?->url() ?? asset('images/image_placeholder.png');
    }
}


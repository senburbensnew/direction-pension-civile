<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actualite extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content_text',
        'category',
        'posted_in',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
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

    public function coverComponentSrc(): string
    {
        $image = $this->images->first();

        return $image?->componentSrc() ?? 'images/image_placeholder.png';
    }
}

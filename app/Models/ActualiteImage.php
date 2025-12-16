<?php

namespace App\Models;

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
}

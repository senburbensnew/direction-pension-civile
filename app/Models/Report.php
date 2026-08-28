<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'year',
        'description',
        'file_name',
        'file_path',
        'cover_path',
        'mime_type',
        'file_size',
        'status',
        'published_at',
        'created_by'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // relation vers user
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // URL public pour téléchargement/affichage
    public function getPublicUrlAttribute()
    {
        // suppose disk 'public' (storage/app/public)
        return Storage::disk('public')->url($this->file_path);
    }

    public function coverUrl(): string
    {
        $path = $this->cover_path;

        if (! $path) {
            return asset('images/image_placeholder.png');
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}

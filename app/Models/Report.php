<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'created_by',
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
        return PublicMedia::preferredUrl(
            PublicMedia::resolve($this->coverComponentSrc())
        );
    }

    public function coverComponentSrc(): string
    {
        if (! $this->cover_path) {
            return 'images/image_placeholder.png';
        }

        return PublicMedia::componentSrc($this->cover_path);
    }
}

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
        'mime_type',
        'file_size',
        'status',
        'published_at',
        'created_by'
    ];

    protected $dates = ['published_at'];

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



}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image', 'status', 'order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}

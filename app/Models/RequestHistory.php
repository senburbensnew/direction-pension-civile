<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'title',
        'description',
        'request_type',
        'request_data',
        'event_type',
        'event_date',
        'created_by',
        "created_at",
        "updated_at",
    ];

    public function scopeForUser($query)
    {
        return $query->where('created_by', auth()->id());
    }
}
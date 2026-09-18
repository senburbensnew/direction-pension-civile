<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'visited_on',
        'hits',
        'visitors',
    ];

    protected $casts = [
        'visited_on' => 'date',
        'hits' => 'integer',
        'visitors' => 'integer',
    ];
}

<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

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

<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PensionType extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;
    use HasFactory;
    
    protected $fillable = [
        'name',
        "created_at",
        "updated_at",
    ];
}

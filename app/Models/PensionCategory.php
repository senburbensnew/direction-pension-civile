<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PensionCategory extends Model
{
    use HasFactory;

    protected $table = 'pension_category';
    
    protected $fillable = [
        'name',
    ];
}

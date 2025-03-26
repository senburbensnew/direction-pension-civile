<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PensionType extends Model
{
    use HasFactory;

    protected $table = 'pension_type';
    
    protected $fillable = [
        'name',
    ];
}

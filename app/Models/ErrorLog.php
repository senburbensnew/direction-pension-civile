<?php

// app/Models/ErrorLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'message',
        'code',
        'file',
        'line',
        'trace',
        'request_data',
        'user_id',
        "created_at",
        "updated_at",
    ];
}
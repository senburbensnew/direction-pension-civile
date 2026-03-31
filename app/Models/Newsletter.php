<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = ['email', 'unsubscribe_token'];

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

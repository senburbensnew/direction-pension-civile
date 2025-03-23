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
        'description'
    ];

    public function request()
    {
        return $this->belongsTo(BankTransferRequests::class, 'request_id');
    }
}
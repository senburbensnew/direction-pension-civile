<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'user_id',
        'assigned_by',
        'note',
        'ended_at',
    ];

    protected $casts = [
        'ended_at' => 'datetime',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignePar()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}

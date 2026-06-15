<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'event',
        'champs',
        'statut',
        'commentaire',
        'changed_by',
    ];

    protected $casts = [
        'champs' => 'array',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function creator()
    {
        if (!isset($this->changed_by)) {
            return null;
        }
        return User::find($this->changed_by);
    }
}

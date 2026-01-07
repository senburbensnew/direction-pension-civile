<?php

namespace App\Models;

use App\Models\User;
use App\Models\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'statut',
        'commentaire',
        'changed_by',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];


    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }


    public function changer()
    {
        return $this->belongsTo(\App\Models\User::class,'changed_by');
    }

    public function creator()
    {
        if (!isset($this->changed_by)) {
            return null;
        }

        return User::find($this->changed_by);
    }

 /*    public function statut()
    {
        if (!isset($this->statut)) {
            return null;
        }

        return Status::find($this->statut);
    } */
}

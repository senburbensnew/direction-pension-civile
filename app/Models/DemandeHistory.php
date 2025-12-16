<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeHistory extends Model
{
    use HasFactory;

    protected $fillable = ['demande_id','etat','commentaire','changed_by'];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }


    public function changer()
    {
        return $this->belongsTo(\App\Models\User::class,'changed_by');
    }
}

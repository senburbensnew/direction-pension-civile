<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RequestHistory;

class BankTransferRequests extends Model
{
    use HasFactory;
    
   // Définition des constantes de statut
   const STATUS_PENDING = 'en_attente';
   const STATUS_APPROVED = 'approuvé';
   const STATUS_IN_PROGRESS = 'en_cours';
   const STATUS_REJECTED = 'rejeté';
   const STATUS_COMPLETED = 'traité';

   public static function getStatusStyle($status)
   {
       return match($status) {
           self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
           self::STATUS_APPROVED => 'bg-blue-100 text-blue-800',
           self::STATUS_IN_PROGRESS => 'bg-purple-100 text-purple-800',
           self::STATUS_REJECTED => 'bg-red-100 text-red-800',
           self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
           default => 'bg-gray-100 text-gray-800'
       };
   }

    protected $fillable = [
        'pensioner_code',
        'code',
        'pension_type',
        'nif',
        'full_name',
        'address',
        'city',
        'birth_date',
        'civil_status',
        'gender',
        'allocation_amount',
        'mother_name',
        'phone',
        'pension_category',
        'bank_name',
        'account_number',
        'account_name',
        'photo_path',
        'created_by',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $attributes = [
        'status' => 'pending',
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

// Scopes principaux
public function scopeForUser($query)
{
    return $query->where('created_by', auth()->id());
}

public function scopePending($query)
{
    return $query->where('status', self::STATUS_PENDING);
}

public function scopeApproved($query)
{
    return $query->where('status', self::STATUS_APPROVED);
}

public function scopeInProgress($query)
{
    return $query->where('status', self::STATUS_IN_PROGRESS);
}

public function scopeRejected($query)
{
    return $query->where('status', self::STATUS_REJECTED);
}

public function scopeCompleted($query)
{
    return $query->where('status', self::STATUS_COMPLETED);
}

// Méthode helper pour les libellés
public static function getStatusLabels()
{
    return [
        self::STATUS_PENDING => 'En attente de traitement',
        self::STATUS_APPROVED => 'Demande approuvée',
        self::STATUS_IN_PROGRESS => 'En cours de traitement',
        self::STATUS_REJECTED => 'Demande rejetée',
        self::STATUS_COMPLETED => 'Traitement finalisé'
    ];
}

// Méthode pour les options de filtre
public static function getStatusOptions()
{
    return collect(self::getStatusLabels())->mapWithKeys(function ($label, $status) {
        return [$status => $label];
    })->toArray();
}

public function history()
{
    return $this->hasMany(RequestHistory::class, 'request_id');
}

}

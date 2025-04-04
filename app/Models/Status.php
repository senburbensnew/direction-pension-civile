<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

   const STATUS_PENDING = 'En attente de traitement';
   const STATUS_APPROVED = 'Demande approuvée';
   const STATUS_IN_PROGRESS = 'En cours de traitement';
   const STATUS_REJECTED = 'Demande rejetée';
   const STATUS_COMPLETED = 'Traitement finalisé';
   const STATUS_CANCELED = 'Demande annulée';

    protected $fillable = [
        'name',
    ];

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

    public static function getStatusOptions()
    {
        return collect(self::getStatusLabels())->mapWithKeys(function ($label, $status) {
            return [$status => $label];
        })->toArray();
    } 

    private static function getStatus(string $statusConstant): Status
    {
        return self::firstOrCreate(['name' => $statusConstant]);
    }

    public static function getStatusPending(): Status
    {
        return self::getStatus(self::STATUS_PENDING);
    }

    public static function getStatusApproved(): Status
    {
        return self::getStatus(self::STATUS_APPROVED);
    }

    public static function getStatusInProgress(): Status
    {
        return self::getStatus(self::STATUS_IN_PROGRESS);
    }

    public static function getStatusRejected(): Status
    {
        return self::getStatus(self::STATUS_REJECTED);
    }

    public static function getStatusCompleted(): Status
    {
        return self::getStatus(self::STATUS_COMPLETED);
    }

    public static function getStatusCanceled(): Status
    {
        return self::getStatus(self::STATUS_CANCELED);
    }
}

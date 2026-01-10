<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Gender;
use App\Models\CivilStatus;
use App\Models\PensionType;
use App\Models\PensionCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'created_by',
        'status_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function civilStatus($name='civil_status_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return CivilStatus::find($this->data[$name]);
    }

    public function gender($name='gender_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return Gender::find($this->data[$name]);
    }

    public function pensionType($name='pension_type_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return PensionType::find($this->data[$name]);
    }

    public function pensionCategory($name='pension_category_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return PensionCategory::find($this->data[$name]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories()
    {
        return $this->hasMany(DemandeHistory::class);
    }

    /* public function parseDate($date)
    {
        return $date ? Carbon::parse($date) : null;
    } */

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function scopeForUser($query)
    {
        return $query->where('created_by', auth()->id());
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }


    public function scopePending($query)
    {
        return $query->where('status_id', Status::getStatusPending()->id);
    }

    public function scopeApproved($query)
    {
        return $query->where('status_id', Status::getStatusApproved()->id);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status_id', Status::getStatusInProgress()->id);
    }

    public function scopeRejected($query)
    {
        return $query->where('status_id', Status::getStatusRejected()->id);
    }

    public function scopeCanceled($query)
    {
        return $query->where('status_id', Status::getStatusCanceled()->id);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status_id', Status::getStatusCompleted()->id);
    }
}

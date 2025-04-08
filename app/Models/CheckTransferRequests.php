<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckTransferRequests extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiscal_year',
        'start_month',
        'request_date',
        'pension_category_id',
        'pensioner_code',
        'amount',
        'lastname',
        'firstname',
        'maiden_name',
        'nif',
        'ninu',
        'address',
        'phone',
        'email',
        'from',
        'to',
        'transfer_reason',
        'code',
        'status_id',
        'created_by',
        'pensioner_signature',
    ];

    public function scopeForUser($query)
    {
        return $query->where('created_by', auth()->id());
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

    public function scopeCompleted($query)
    {
        return $query->where('status_id', Status::getStatusCompleted()->id);
    }             

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pensionCategory()
    {
        return $this->belongsTo(PensionCategory::class, 'pension_category_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}

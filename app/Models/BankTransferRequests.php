<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RequestHistory;

class BankTransferRequests extends Model
{
    use HasFactory;

    protected $fillable = [
        'pensioner_code',
        'code',
        'pension_type_id',
        'nif',
        'full_name',
        'address',
        'city',
        'birth_date',
        'civil_status_id',
        'gender_id',
        'allocation_amount',
        'mother_name',
        'phone',
        'pension_category_id',
        'bank_name',
        'account_number',
        'account_name',
        'photo_path',
        'created_by',
        'status_id',
        "created_at",
        "updated_at",
    ];

    protected $casts = [
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function pensionType()
    {
        return $this->belongsTo(PensionType::class, 'pension_type_id');
    }

    public function civilStatus()
    {
        return $this->belongsTo(CivilStatus::class, 'civil_status_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function pensionCategory()
    {
        return $this->belongsTo(PensionCategory::class, 'pension_category_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /* public function history()
        {
            return $this->hasMany(RequestHistory::class, 'request_id');
        } 
    */
}

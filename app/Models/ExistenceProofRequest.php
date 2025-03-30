<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExistenceProofRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        "status_id",
        "created_by",
        "pension_amount",
        "code",
        "id_number",
        "profile_photo",
        "fiscal_year",
        "nif",
        "lastname",
        "firstname",
        "address",
        "location",
        "birth_date",
        "civil_status_id",
        "pension_category_id",
        "gender_id",
        "postal_address",
        "phone",
        "amount",
        "monitor_number",
        "monitor_date",
        "pension_start_date",
        "pension_end_date",
        "pension_nature"
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

    public function civilStatus()
    {
        return $this->belongsTo(CivilStatus::class, 'civil_status_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}

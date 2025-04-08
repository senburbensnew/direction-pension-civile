<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PensionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nif',
        'photos',
        'career_certificate',
        'monitor_copy',
        'marriage_certificate',
        'birth_certificate',
        'divorce_certificate',
        'tax_id_number',
        'medical_certificate',
        'check_stub',
        'status_id',
        'created_at',
        'created_by',
    ];

/*     protected $attributes = [
        'status_id' => Status::getStatusPending()->id 
    ]; */

    protected $casts = [
        'photos' => 'array' 
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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

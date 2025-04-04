<?php

namespace App\Models;

use App\Enums\RequestEventTypeEnum;
use App\Enums\RequestTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'request_type',
        'request_data',
        'event_type',
        'event_date',
        'created_by',
    ];

    public function scopeForUser($query)
    {
        return $query->where('created_by', auth()->id());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function store (
        $requestId,
        RequestTypeEnum $requestType,
        $requestData,  // Expect pre-sanitized JSON
        RequestEventTypeEnum $eventType,
        $eventDate,
        $byUserId          // Enforce valid user ID
    ){
        $requestHistory = RequestHistory::create([
            'request_id' => $requestId,
            'request_type' => $requestType->value,
            'request_data' => $requestData,
            'event_type' => $eventType->value,
            'event_date' => $eventDate,
            'created_by' => $byUserId
        ]);

        return $requestHistory;
    }
}
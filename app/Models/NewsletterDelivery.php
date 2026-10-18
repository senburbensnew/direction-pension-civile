<?php

namespace App\Models;

use App\Enums\NewsletterDeliveryStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterDelivery extends Model
{
    protected $fillable = [
        'newsletter_campaign_id',
        'newsletter_id',
        'email',
        'status',
        'tracking_token',
        'provider_message_id',
        'attempts',
        'failure_reason',
        'sent_at',
        'delivered_at',
        'failed_at',
        'opened_at',
        'open_count',
        'click_count',
    ];

    protected $casts = [
        'status' => NewsletterDeliveryStatusEnum::class,
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'newsletter_campaign_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class, 'newsletter_id');
    }

    public static function generateTrackingToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

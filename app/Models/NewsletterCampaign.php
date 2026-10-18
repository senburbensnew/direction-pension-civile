<?php

namespace App\Models;

use App\Enums\NewsletterCampaignStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NewsletterCampaign extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'status',
        'subject',
        'preview_text',
        'from_name',
        'reply_to',
        'body',
        'html_body',
        'audience_filter',
        'recipients_count',
        'count_queued',
        'count_sent',
        'count_delivered',
        'count_failed',
        'count_bounced',
        'count_opened',
        'count_clicked',
        'sent_by',
        'sent_at',
        'scheduled_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'duplicated_from_id',
        'dispatch_token',
        'open_tracking_enabled',
        'click_tracking_enabled',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'open_tracking_enabled' => 'boolean',
        'click_tracking_enabled' => 'boolean',
        'status' => NewsletterCampaignStatusEnum::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status', 'subject', 'scheduled_at', 'sent_at', 'recipients_count',
                'count_sent', 'count_failed', 'cancelled_at',
            ])
            ->logOnlyDirty()
            ->useLogName('newsletter_campaign');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function duplicatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicated_from_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class, 'newsletter_campaign_id');
    }

    public function statusEnum(): NewsletterCampaignStatusEnum
    {
        return $this->status ?? NewsletterCampaignStatusEnum::Draft;
    }

    public function isEditable(): bool
    {
        return $this->statusEnum()->isEditable();
    }

    public function renderHtmlBody(): string
    {
        if ($this->html_body) {
            return $this->html_body;
        }

        return nl2br(e($this->body ?? ''));
    }
}

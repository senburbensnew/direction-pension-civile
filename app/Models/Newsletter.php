<?php

namespace App\Models;

use App\Enums\NewsletterSubscriberStatusEnum;
use App\Support\Newsletter\NewsletterEmailNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Newsletter extends Model
{
    protected $fillable = [
        'email',
        'status',
        'locale',
        'audience_group',
        'preferences',
        'user_id',
        'confirmed_at',
        'unsubscribed_at',
        'consent_source',
        'consent_ip',
        'consented_at',
        'unsubscribe_token',
        'confirmation_token',
        'confirmation_token_expires_at',
    ];

    protected $casts = [
        'preferences' => 'array',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'consented_at' => 'datetime',
        'confirmation_token_expires_at' => 'datetime',
        'status' => NewsletterSubscriberStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->email = NewsletterEmailNormalizer::normalize($model->email);
        });
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->where('status', NewsletterSubscriberStatusEnum::Subscribed);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at');
    }

    public function scopeMailable(Builder $query): Builder
    {
        return $query->subscribed()->confirmed();
    }

    public function isMailable(): bool
    {
        return $this->status === NewsletterSubscriberStatusEnum::Subscribed
            && $this->confirmed_at !== null
            && ! NewsletterSuppression::isSuppressed($this->email);
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '—';
    }
}

<?php

namespace App\Models;

use App\Enums\NewsletterSuppressionReasonEnum;
use App\Support\Newsletter\NewsletterEmailNormalizer;
use Illuminate\Database\Eloquent\Model;

class NewsletterSuppression extends Model
{
    protected $fillable = [
        'email',
        'reason',
        'meta',
        'suppressed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'suppressed_at' => 'datetime',
        'reason' => NewsletterSuppressionReasonEnum::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->email = NewsletterEmailNormalizer::normalize($model->email);
        });
    }

    public static function suppress(string $email, NewsletterSuppressionReasonEnum $reason, ?array $meta = null): self
    {
        return self::updateOrCreate(
            ['email' => NewsletterEmailNormalizer::normalize($email)],
            [
                'reason' => $reason,
                'meta' => $meta,
                'suppressed_at' => now(),
            ]
        );
    }

    public static function isSuppressed(string $email): bool
    {
        return self::query()
            ->where('email', NewsletterEmailNormalizer::normalize($email))
            ->exists();
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\NewsletterSuppressionReasonEnum;
use App\Support\Newsletter\NewsletterEmailNormalizer;
use Illuminate\Database\Eloquent\Model;

class NewsletterSuppression extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

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
        $normalized = NewsletterEmailNormalizer::normalize($email);

        /** @var self $suppression */
        $suppression = self::withTrashed()->firstOrNew(['email' => $normalized]);

        if ($suppression->trashed()) {
            $suppression->restore();
        }

        $suppression->fill([
            'reason' => $reason,
            'meta' => $meta,
            'suppressed_at' => now(),
        ]);
        $suppression->save();

        return $suppression;
    }

    public static function isSuppressed(string $email): bool
    {
        return self::query()
            ->where('email', NewsletterEmailNormalizer::normalize($email))
            ->exists();
    }
}

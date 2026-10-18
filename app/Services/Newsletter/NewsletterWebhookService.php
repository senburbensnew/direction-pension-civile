<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterDeliveryStatusEnum;
use App\Enums\NewsletterSubscriberStatusEnum;
use App\Enums\NewsletterSuppressionReasonEnum;
use App\Models\Newsletter;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSuppression;
use App\Support\Newsletter\NewsletterEmailNormalizer;

class NewsletterWebhookService
{
    public function handleBounce(string $email, ?string $providerMessageId = null, ?array $meta = null): void
    {
        $email = NewsletterEmailNormalizer::normalize($email);

        NewsletterSuppression::suppress($email, NewsletterSuppressionReasonEnum::Bounced, $meta);

        Newsletter::query()->where('email', $email)->update([
            'status' => NewsletterSubscriberStatusEnum::Bounced,
        ]);

        if ($providerMessageId) {
            NewsletterDelivery::query()
                ->where('provider_message_id', $providerMessageId)
                ->update([
                    'status' => NewsletterDeliveryStatusEnum::Bounced,
                    'failed_at' => now(),
                    'failure_reason' => 'Bounce webhook',
                ]);
        }
    }

    public function handleDelivered(?string $providerMessageId): void
    {
        if (! $providerMessageId) {
            return;
        }

        NewsletterDelivery::query()
            ->where('provider_message_id', $providerMessageId)
            ->update([
                'status' => NewsletterDeliveryStatusEnum::Delivered,
                'delivered_at' => now(),
            ]);
    }
}

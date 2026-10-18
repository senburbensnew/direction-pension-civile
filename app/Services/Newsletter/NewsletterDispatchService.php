<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterCampaignStatusEnum;
use App\Enums\NewsletterDeliveryStatusEnum;
use App\Jobs\SendNewsletterDeliveryJob;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use Illuminate\Support\Facades\DB;

class NewsletterDispatchService
{
    public function __construct(
        private readonly NewsletterAudienceService $audience,
    ) {}

    public function dispatchNow(NewsletterCampaign $campaign, ?int $userId = null): NewsletterCampaign
    {
        return $this->beginDispatch($campaign, $userId, scheduled: false);
    }

    public function dispatchDueScheduled(NewsletterCampaign $campaign): NewsletterCampaign
    {
        return $this->beginDispatch($campaign, $campaign->sent_by, scheduled: true);
    }

    private function beginDispatch(NewsletterCampaign $campaign, ?int $userId, bool $scheduled): NewsletterCampaign
    {
        return DB::transaction(function () use ($campaign, $userId, $scheduled) {
            $locked = NewsletterCampaign::query()->lockForUpdate()->findOrFail($campaign->id);

            if ($locked->status === NewsletterCampaignStatusEnum::Sending) {
                throw new \RuntimeException('Campaign is already sending.');
            }

            if ($locked->status === NewsletterCampaignStatusEnum::Sent) {
                throw new \RuntimeException('Campaign was already sent.');
            }

            if ($scheduled && $locked->status !== NewsletterCampaignStatusEnum::Scheduled) {
                throw new \RuntimeException('Campaign is not scheduled.');
            }

            if (! $scheduled && ! in_array($locked->statusEnum(), [NewsletterCampaignStatusEnum::Draft, NewsletterCampaignStatusEnum::Scheduled], true)) {
                throw new \RuntimeException('Campaign cannot be sent.');
            }

            if ($locked->dispatch_token) {
                throw new \RuntimeException('Duplicate dispatch prevented.');
            }

            $recipients = $this->audience->recipients($locked->audience_filter);
            if ($recipients->isEmpty()) {
                throw new \RuntimeException('No mailable recipients.');
            }

            $token = bin2hex(random_bytes(32));

            $locked->update([
                'status' => NewsletterCampaignStatusEnum::Sending,
                'dispatch_token' => $token,
                'started_at' => now(),
                'sent_by' => $userId ?? $locked->sent_by,
                'recipients_count' => $recipients->count(),
                'count_queued' => $recipients->count(),
                'scheduled_at' => $scheduled ? $locked->scheduled_at : null,
            ]);

            foreach ($recipients as $subscriber) {
                $delivery = NewsletterDelivery::create([
                    'newsletter_campaign_id' => $locked->id,
                    'newsletter_id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'status' => NewsletterDeliveryStatusEnum::Queued,
                    'tracking_token' => NewsletterDelivery::generateTrackingToken(),
                ]);

                SendNewsletterDeliveryJob::dispatch($delivery->id)
                    ->delay(now()->addSeconds(
                        (int) (floor($delivery->id / config('newsletter.batch_size', 50)) * config('newsletter.batch_delay_seconds', 2))
                    ));
            }

            return $locked->fresh();
        });
    }

    public function refreshCampaignStats(NewsletterCampaign $campaign): void
    {
        $counts = NewsletterDelivery::query()
            ->where('newsletter_campaign_id', $campaign->id)
            ->selectRaw("
                SUM(CASE WHEN status = 'queued' THEN 1 ELSE 0 END) as queued,
                SUM(CASE WHEN status IN ('sent','delivered') THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'bounced' THEN 1 ELSE 0 END) as bounced,
                SUM(CASE WHEN open_count > 0 THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN click_count > 0 THEN 1 ELSE 0 END) as clicked
            ")
            ->first();

        $stillQueued = (int) ($counts->queued ?? 0);

        $updates = [
            'count_queued' => (int) ($counts->queued ?? 0),
            'count_sent' => (int) ($counts->sent ?? 0),
            'count_delivered' => (int) ($counts->delivered ?? 0),
            'count_failed' => (int) ($counts->failed ?? 0),
            'count_bounced' => (int) ($counts->bounced ?? 0),
            'count_opened' => (int) ($counts->opened ?? 0),
            'count_clicked' => (int) ($counts->clicked ?? 0),
        ];

        if ($stillQueued === 0 && $campaign->status === NewsletterCampaignStatusEnum::Sending) {
            $updates['status'] = NewsletterCampaignStatusEnum::Sent;
            $updates['sent_at'] = $campaign->sent_at ?? now();
            $updates['completed_at'] = now();
        }

        $campaign->update($updates);
    }
}

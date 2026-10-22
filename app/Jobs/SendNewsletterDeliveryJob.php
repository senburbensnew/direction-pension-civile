<?php

namespace App\Jobs;

use App\Enums\NewsletterDeliveryStatusEnum;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSuppression;
use App\Notifications\NewsletterCampaignNotification;
use App\Services\Newsletter\NewsletterDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendNewsletterDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public function __construct(public readonly int $deliveryId)
    {
        $this->tries = (int) config('queue.max_tries', 5);
    }

    public function handle(NewsletterDispatchService $dispatch): void
    {
        $delivery = NewsletterDelivery::query()->with(['campaign', 'subscriber'])->find($this->deliveryId);

        if (! $delivery || ! $delivery->campaign) {
            return;
        }

        if ($delivery->status !== NewsletterDeliveryStatusEnum::Queued) {
            return;
        }

        if (NewsletterSuppression::isSuppressed($delivery->email)) {
            $delivery->update([
                'status' => NewsletterDeliveryStatusEnum::Suppressed,
                'failed_at' => now(),
                'failure_reason' => 'Suppressed',
            ]);
            $dispatch->refreshCampaignStats($delivery->campaign);

            return;
        }

        $subscriber = $delivery->subscriber;
        if (! $subscriber || ! $subscriber->isMailable()) {
            $delivery->update([
                'status' => NewsletterDeliveryStatusEnum::Suppressed,
                'failed_at' => now(),
                'failure_reason' => 'Not mailable',
            ]);
            $dispatch->refreshCampaignStats($delivery->campaign);

            return;
        }

        $delivery->increment('attempts');

        try {
            Notification::route('mail', $delivery->email)
                ->notify(new NewsletterCampaignNotification(
                    $delivery->campaign,
                    $subscriber,
                    $delivery,
                ));

            $delivery->update([
                'status' => NewsletterDeliveryStatusEnum::Sent,
                'sent_at' => now(),
                'delivered_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Newsletter delivery failed', [
                'delivery_id' => $delivery->id,
                'email' => $delivery->email,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $delivery->update([
                    'status' => NewsletterDeliveryStatusEnum::Failed,
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);
            }

            throw $e;
        } finally {
            $dispatch->refreshCampaignStats($delivery->campaign);
        }
    }
}

<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterDelivery;
use Illuminate\Support\Facades\URL;

class NewsletterTrackingService
{
    public function openPixelUrl(NewsletterDelivery $delivery): ?string
    {
        if (! $delivery->campaign?->open_tracking_enabled) {
            return null;
        }

        return URL::route('newsletter.track.open', ['token' => $delivery->tracking_token]);
    }

    public function wrapLink(NewsletterDelivery $delivery, string $url): string
    {
        if (! $delivery->campaign?->click_tracking_enabled) {
            return $url;
        }

        return URL::route('newsletter.track.click', [
            'token' => $delivery->tracking_token,
            'url' => base64_encode($url),
        ]);
    }

    public function recordOpen(string $token): void
    {
        $delivery = NewsletterDelivery::query()->where('tracking_token', $token)->first();
        if (! $delivery) {
            return;
        }

        $delivery->increment('open_count');
        if (! $delivery->opened_at) {
            $delivery->update(['opened_at' => now()]);
        }

        if ($delivery->campaign) {
            app(NewsletterDispatchService::class)->refreshCampaignStats($delivery->campaign);
        }
    }

    public function recordClick(string $token): ?string
    {
        $delivery = NewsletterDelivery::query()->where('tracking_token', $token)->first();
        if (! $delivery) {
            return null;
        }

        $delivery->increment('click_count');

        if ($delivery->campaign) {
            app(NewsletterDispatchService::class)->refreshCampaignStats($delivery->campaign);
        }

        return null;
    }
}

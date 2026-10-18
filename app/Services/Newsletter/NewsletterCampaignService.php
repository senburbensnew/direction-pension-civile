<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterCampaignStatusEnum;
use App\Models\NewsletterCampaign;
use App\Support\Newsletter\NewsletterHtmlSanitizer;
use Illuminate\Support\Arr;

class NewsletterCampaignService
{
    public function __construct(
        private readonly NewsletterAudienceService $audience,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(array $data, ?int $userId = null): NewsletterCampaign
    {
        $filter = $this->normalizeAudienceFilter($data['audience_filter'] ?? null);

        return NewsletterCampaign::create([
            'status' => NewsletterCampaignStatusEnum::Draft,
            'subject' => $data['subject'] ?? '',
            'preview_text' => $data['preview_text'] ?? null,
            'from_name' => $data['from_name'] ?? config('newsletter.from_name'),
            'reply_to' => $data['reply_to'] ?? config('newsletter.reply_to'),
            'html_body' => NewsletterHtmlSanitizer::sanitize($data['html_body'] ?? $data['body'] ?? ''),
            'body' => strip_tags($data['html_body'] ?? $data['body'] ?? ''),
            'audience_filter' => $filter,
            'recipients_count' => $this->audience->count($filter),
            'open_tracking_enabled' => (bool) ($data['open_tracking_enabled'] ?? config('newsletter.open_tracking')),
            'click_tracking_enabled' => (bool) ($data['click_tracking_enabled'] ?? config('newsletter.click_tracking')),
            'sent_by' => $userId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(NewsletterCampaign $campaign, array $data): NewsletterCampaign
    {
        if (! $campaign->isEditable()) {
            throw new \RuntimeException('Campaign is not editable.');
        }

        $filter = $this->normalizeAudienceFilter($data['audience_filter'] ?? $campaign->audience_filter);

        $campaign->update([
            'subject' => $data['subject'] ?? $campaign->subject,
            'preview_text' => $data['preview_text'] ?? $campaign->preview_text,
            'from_name' => $data['from_name'] ?? $campaign->from_name,
            'reply_to' => $data['reply_to'] ?? $campaign->reply_to,
            'html_body' => NewsletterHtmlSanitizer::sanitize($data['html_body'] ?? $campaign->html_body),
            'body' => strip_tags($data['html_body'] ?? $campaign->html_body ?? $campaign->body ?? ''),
            'audience_filter' => $filter,
            'recipients_count' => $this->audience->count($filter),
            'open_tracking_enabled' => array_key_exists('open_tracking_enabled', $data)
                ? (bool) $data['open_tracking_enabled']
                : $campaign->open_tracking_enabled,
            'click_tracking_enabled' => array_key_exists('click_tracking_enabled', $data)
                ? (bool) $data['click_tracking_enabled']
                : $campaign->click_tracking_enabled,
        ]);

        return $campaign->fresh();
    }

    public function duplicate(NewsletterCampaign $campaign, ?int $userId = null): NewsletterCampaign
    {
        $copy = $campaign->replicate([
            'status', 'sent_at', 'scheduled_at', 'started_at', 'completed_at', 'cancelled_at',
            'dispatch_token', 'count_queued', 'count_sent', 'count_delivered', 'count_failed',
            'count_bounced', 'count_opened', 'count_clicked',
        ]);

        $copy->status = NewsletterCampaignStatusEnum::Draft;
        $copy->duplicated_from_id = $campaign->id;
        $copy->sent_by = $userId;
        $copy->subject = $campaign->subject.' (copie)';
        $copy->save();

        return $copy;
    }

    public function schedule(NewsletterCampaign $campaign, \DateTimeInterface $when, ?int $userId = null): NewsletterCampaign
    {
        if (! $campaign->isEditable()) {
            throw new \RuntimeException('Campaign cannot be scheduled.');
        }

        $campaign->update([
            'status' => NewsletterCampaignStatusEnum::Scheduled,
            'scheduled_at' => $when,
            'sent_by' => $userId ?? $campaign->sent_by,
        ]);

        return $campaign->fresh();
    }

    public function cancel(NewsletterCampaign $campaign): NewsletterCampaign
    {
        if (! in_array($campaign->statusEnum(), [NewsletterCampaignStatusEnum::Scheduled, NewsletterCampaignStatusEnum::Draft], true)) {
            throw new \RuntimeException('Campaign cannot be cancelled.');
        }

        $campaign->update([
            'status' => NewsletterCampaignStatusEnum::Cancelled,
            'cancelled_at' => now(),
            'scheduled_at' => null,
        ]);

        return $campaign->fresh();
    }

    /**
     * @param  array<string, mixed>|null  $raw
     * @return array<string, mixed>|null
     */
    private function normalizeAudienceFilter(?array $raw): ?array
    {
        if ($raw === null || $raw === []) {
            return null;
        }

        return [
            'locales' => array_values(array_filter(Arr::wrap($raw['locales'] ?? []))),
            'audience_groups' => array_values(array_filter(Arr::wrap($raw['audience_groups'] ?? []))),
            'preferences_any' => array_values(array_filter(Arr::wrap($raw['preferences_any'] ?? []))),
        ];
    }
}

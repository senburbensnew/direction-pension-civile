<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterSubscriberStatusEnum;
use App\Enums\NewsletterSuppressionReasonEnum;
use App\Models\Newsletter;
use App\Models\NewsletterSuppression;
use App\Notifications\NewsletterConfirmSubscriptionNotification;
use App\Support\Newsletter\NewsletterEmailNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NewsletterSubscriptionService
{
    public function subscribe(
        string $email,
        ?Request $request = null,
        ?string $locale = null,
        ?string $audienceGroup = null,
        ?array $preferences = null,
        ?int $userId = null,
        string $consentSource = 'public_form',
    ): NewsletterSubscribeResult {
        $email = NewsletterEmailNormalizer::normalize($email);

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return NewsletterSubscribeResult::invalid(__('newsletter.invalid_email'));
        }

        if (NewsletterSuppression::isSuppressed($email)) {
            $existing = Newsletter::query()->where('email', $email)->first();
            if ($existing && $existing->status === NewsletterSubscriberStatusEnum::Unsubscribed) {
                return NewsletterSubscribeResult::resubscribeRequired(__('newsletter.resubscribe_required'));
            }

            return NewsletterSubscribeResult::blocked(__('newsletter.suppressed'));
        }

        $subscriber = Newsletter::query()->where('email', $email)->first();

        if ($subscriber) {
            if ($subscriber->status === NewsletterSubscriberStatusEnum::Subscribed && $subscriber->confirmed_at) {
                return NewsletterSubscribeResult::duplicate(__('newsletter.already_subscribed'));
            }

            if ($subscriber->status === NewsletterSubscriberStatusEnum::Unsubscribed) {
                return NewsletterSubscribeResult::resubscribeRequired(__('newsletter.resubscribe_required'));
            }

            if ($subscriber->status === NewsletterSubscriberStatusEnum::Pending) {
                $this->refreshPendingConfirmation($subscriber);

                return NewsletterSubscribeResult::pending(__('newsletter.confirmation_resent'));
            }
        }

        $doubleOptIn = (bool) config('newsletter.double_opt_in', true);
        $status = $doubleOptIn
            ? NewsletterSubscriberStatusEnum::Pending
            : NewsletterSubscriberStatusEnum::Subscribed;

        $data = [
            'email' => $email,
            'status' => $status,
            'locale' => $locale ?: app()->getLocale(),
            'audience_group' => $audienceGroup ?: 'externe',
            'preferences' => $preferences ?: [],
            'user_id' => $userId,
            'consent_source' => $consentSource,
            'consent_ip' => $request?->ip(),
            'consented_at' => now(),
            'unsubscribe_token' => Newsletter::generateToken(),
            'unsubscribed_at' => null,
        ];

        if ($doubleOptIn) {
            $data['confirmation_token'] = Newsletter::generateToken();
            $data['confirmation_token_expires_at'] = now()->addHours((int) config('newsletter.confirmation_ttl_hours', 48));
            $data['confirmed_at'] = null;
        } else {
            $data['confirmation_token'] = null;
            $data['confirmation_token_expires_at'] = null;
            $data['confirmed_at'] = now();
        }

        $subscriber = $subscriber
            ? tap($subscriber)->update($data)
            : Newsletter::create($data);

        if ($doubleOptIn) {
            Notification::route('mail', $subscriber->email)
                ->notify(new NewsletterConfirmSubscriptionNotification($subscriber));
        }

        return $doubleOptIn
            ? NewsletterSubscribeResult::pending(__('newsletter.confirmation_sent'))
            : NewsletterSubscribeResult::subscribed(__('newsletter.subscribed'));
    }

    public function confirm(string $token): NewsletterConfirmResult
    {
        $subscriber = Newsletter::query()->where('confirmation_token', $token)->first();

        if (! $subscriber) {
            return NewsletterConfirmResult::invalid(__('newsletter.confirm_invalid'));
        }

        if ($subscriber->confirmation_token_expires_at && $subscriber->confirmation_token_expires_at->isPast()) {
            return NewsletterConfirmResult::expired(__('newsletter.confirm_expired'));
        }

        if (NewsletterSuppression::isSuppressed($subscriber->email)) {
            return NewsletterConfirmResult::blocked(__('newsletter.suppressed'));
        }

        $subscriber->update([
            'status' => NewsletterSubscriberStatusEnum::Subscribed,
            'confirmed_at' => now(),
            'confirmation_token' => null,
            'confirmation_token_expires_at' => null,
        ]);

        return NewsletterConfirmResult::success(__('newsletter.confirm_success'));
    }

    public function unsubscribe(string $token): bool
    {
        $subscriber = Newsletter::query()->where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return false;
        }

        $subscriber->update([
            'status' => NewsletterSubscriberStatusEnum::Unsubscribed,
            'unsubscribed_at' => now(),
        ]);

        NewsletterSuppression::suppress(
            $subscriber->email,
            NewsletterSuppressionReasonEnum::Unsubscribed,
            ['newsletter_id' => $subscriber->id]
        );

        return true;
    }

    public function resubscribe(string $email, ?Request $request = null): NewsletterSubscribeResult
    {
        $email = NewsletterEmailNormalizer::normalize($email);
        $subscriber = Newsletter::query()->where('email', $email)->first();

        if (! $subscriber || $subscriber->status !== NewsletterSubscriberStatusEnum::Unsubscribed) {
            return $this->subscribe($email, $request, consentSource: 'resubscribe');
        }

        NewsletterSuppression::query()->where('email', $email)->delete();

        return $this->subscribe(
            $email,
            $request,
            locale: $subscriber->locale,
            audienceGroup: $subscriber->audience_group,
            preferences: $subscriber->preferences,
            userId: $subscriber->user_id,
            consentSource: 'resubscribe',
        );
    }

    private function refreshPendingConfirmation(Newsletter $subscriber): void
    {
        $subscriber->update([
            'confirmation_token' => Newsletter::generateToken(),
            'confirmation_token_expires_at' => now()->addHours((int) config('newsletter.confirmation_ttl_hours', 48)),
        ]);

        Notification::route('mail', $subscriber->email)
            ->notify(new NewsletterConfirmSubscriptionNotification($subscriber));
    }
}

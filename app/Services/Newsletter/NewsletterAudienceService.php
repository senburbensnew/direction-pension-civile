<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterSubscriberStatusEnum;
use App\Models\Newsletter;
use App\Models\NewsletterSuppression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NewsletterAudienceService
{
    /**
     * @param  array<string, mixed>|null  $filter
     */
    public function query(?array $filter = null): Builder
    {
        $filter = $filter ?? [];

        $query = Newsletter::query()
            ->where('status', NewsletterSubscriberStatusEnum::Subscribed)
            ->whereNotNull('confirmed_at');

        if (! empty($filter['locales']) && is_array($filter['locales'])) {
            $query->whereIn('locale', $filter['locales']);
        }

        if (! empty($filter['audience_groups']) && is_array($filter['audience_groups'])) {
            $query->whereIn('audience_group', $filter['audience_groups']);
        }

        if (! empty($filter['preferences_any']) && is_array($filter['preferences_any'])) {
            $topics = $filter['preferences_any'];
            $query->where(function (Builder $inner) use ($topics) {
                foreach ($topics as $topic) {
                    $inner->orWhereJsonContains('preferences', $topic);
                }
            });
        }

        $suppressed = NewsletterSuppression::query()->pluck('email');
        if ($suppressed->isNotEmpty()) {
            $query->whereNotIn('email', $suppressed);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>|null  $filter
     */
    public function recipients(?array $filter = null): Collection
    {
        return $this->query($filter)->orderBy('id')->get();
    }

    public function count(?array $filter = null): int
    {
        return $this->query($filter)->count();
    }
}

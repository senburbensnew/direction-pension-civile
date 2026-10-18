<?php

namespace App\Console\Commands;

use App\Enums\NewsletterCampaignStatusEnum;
use App\Models\NewsletterCampaign;
use App\Services\Newsletter\NewsletterDispatchService;
use Illuminate\Console\Command;

class DispatchScheduledNewsletterCampaigns extends Command
{
    protected $signature = 'newsletter:dispatch-scheduled';

    protected $description = 'Send newsletter campaigns whose scheduled time has passed';

    public function handle(NewsletterDispatchService $dispatch): int
    {
        $due = NewsletterCampaign::query()
            ->where('status', NewsletterCampaignStatusEnum::Scheduled)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        foreach ($due as $campaign) {
            try {
                $dispatch->dispatchDueScheduled($campaign);
                $this->info("Dispatched campaign #{$campaign->id}");
            } catch (\Throwable $e) {
                $this->error("Campaign #{$campaign->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}

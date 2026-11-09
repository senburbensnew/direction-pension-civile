<?php

namespace Tests\Unit\Enums;

use App\Enums\NewsletterCampaignStatusEnum;
use Tests\TestCase;

class NewsletterCampaignStatusEnumTest extends TestCase
{
    /** @test */
    public function only_draft_and_scheduled_are_editable(): void
    {
        $editable = [NewsletterCampaignStatusEnum::Draft, NewsletterCampaignStatusEnum::Scheduled];

        foreach (NewsletterCampaignStatusEnum::cases() as $case) {
            $this->assertSame(
                in_array($case, $editable, true),
                $case->isEditable(),
                $case->value
            );
        }
    }
}

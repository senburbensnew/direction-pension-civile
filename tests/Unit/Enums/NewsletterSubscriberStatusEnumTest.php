<?php

namespace Tests\Unit\Enums;

use App\Enums\NewsletterSubscriberStatusEnum;
use Tests\TestCase;

class NewsletterSubscriberStatusEnumTest extends TestCase
{
    /** @test */
    public function all_cases_have_label_and_badge(): void
    {
        foreach (NewsletterSubscriberStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
            $this->assertNotEmpty($case->badgeClass(), $case->value);
        }
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\NewsletterDeliveryStatusEnum;
use Tests\TestCase;

class NewsletterDeliveryStatusEnumTest extends TestCase
{
    /** @test */
    public function all_cases_have_french_labels(): void
    {
        foreach (NewsletterDeliveryStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
        }
    }

    /** @test */
    public function try_from_round_trips_stored_values(): void
    {
        foreach (NewsletterDeliveryStatusEnum::cases() as $case) {
            $this->assertSame($case, NewsletterDeliveryStatusEnum::tryFrom($case->value));
        }
    }
}

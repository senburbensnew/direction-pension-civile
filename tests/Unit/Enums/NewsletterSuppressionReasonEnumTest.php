<?php

namespace Tests\Unit\Enums;

use App\Enums\NewsletterSuppressionReasonEnum;
use Tests\TestCase;

class NewsletterSuppressionReasonEnumTest extends TestCase
{
    /** @test */
    public function all_cases_have_french_labels(): void
    {
        foreach (NewsletterSuppressionReasonEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
        }
    }
}

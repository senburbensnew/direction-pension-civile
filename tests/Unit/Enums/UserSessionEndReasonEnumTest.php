<?php

namespace Tests\Unit\Enums;

use App\Enums\UserSessionEndReasonEnum;
use Tests\TestCase;

class UserSessionEndReasonEnumTest extends TestCase
{
    /** @test */
    public function all_cases_have_translated_labels(): void
    {
        foreach (UserSessionEndReasonEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
        }
    }

    /** @test */
    public function stored_values_are_unique_slugs(): void
    {
        $values = array_map(
            static fn (UserSessionEndReasonEnum $c) => $c->value,
            UserSessionEndReasonEnum::cases()
        );

        $this->assertSame($values, array_values(array_unique($values)));
    }
}

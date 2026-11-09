<?php

namespace Tests\Unit\Enums;

use App\Enums\UserTypeEnum;
use Tests\TestCase;

class UserTypeEnumTest extends TestCase
{
    /** @test */
    public function system_codes_lists_all_case_values(): void
    {
        $this->assertSame(
            array_column(UserTypeEnum::cases(), 'value'),
            UserTypeEnum::systemCodes()
        );
    }
}

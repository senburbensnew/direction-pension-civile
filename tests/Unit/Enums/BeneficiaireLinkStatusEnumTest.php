<?php

namespace Tests\Unit\Enums;

use App\Enums\BeneficiaireLinkStatusEnum;
use Tests\TestCase;

class BeneficiaireLinkStatusEnumTest extends TestCase
{
    /** @test */
    public function only_active_status_grants_access(): void
    {
        foreach (BeneficiaireLinkStatusEnum::cases() as $case) {
            $this->assertSame(
                $case === BeneficiaireLinkStatusEnum::ACTIVE,
                $case->grantsAccess(),
                $case->value
            );
        }
    }

    /** @test */
    public function all_cases_have_labels(): void
    {
        foreach (BeneficiaireLinkStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
        }
    }
}

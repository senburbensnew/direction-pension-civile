<?php

namespace Tests\Unit\Enums;

use App\Enums\BeneficiaireLinkPermissionEnum;
use Tests\TestCase;

class BeneficiaireLinkPermissionEnumTest extends TestCase
{
    /** @test */
    public function default_grant_includes_all_permissions(): void
    {
        $this->assertSame(
            array_column(BeneficiaireLinkPermissionEnum::cases(), 'value'),
            BeneficiaireLinkPermissionEnum::defaultGrant()
        );
    }

    /** @test */
    public function all_cases_have_french_labels(): void
    {
        foreach (BeneficiaireLinkPermissionEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
        }
    }
}

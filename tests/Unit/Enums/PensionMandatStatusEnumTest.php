<?php

namespace Tests\Unit\Enums;

use App\Enums\PensionMandatStatusEnum;
use Tests\TestCase;

class PensionMandatStatusEnumTest extends TestCase
{
    /** @test */
    public function only_actif_allows_access(): void
    {
        foreach (PensionMandatStatusEnum::cases() as $case) {
            $this->assertSame(
                $case === PensionMandatStatusEnum::ACTIF,
                $case->allowsAccess(),
                $case->value
            );
        }
    }

    /** @test */
    public function only_en_attente_is_pending(): void
    {
        foreach (PensionMandatStatusEnum::cases() as $case) {
            $this->assertSame(
                $case === PensionMandatStatusEnum::EN_ATTENTE,
                $case->isPending(),
                $case->value
            );
        }
    }

    /** @test */
    public function all_cases_have_labels(): void
    {
        foreach (PensionMandatStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), "Empty label for {$case->value}");
        }
    }
}

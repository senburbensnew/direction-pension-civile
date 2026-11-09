<?php

namespace Tests\Unit\Enums;

use App\Enums\DemandeStatusEnum;
use Tests\TestCase;

class DemandeStatusEnumTest extends TestCase
{
    /** @test */
    public function it_exposes_core_demande_lifecycle_values(): void
    {
        $this->assertSame('BROUILLON', DemandeStatusEnum::BROUILLON->value);
        $this->assertSame('SOUMISE', DemandeStatusEnum::SOUMISE->value);
        $this->assertSame('COMPLEMENT_REQUIS', DemandeStatusEnum::COMPLEMENT_REQUIS->value);
    }

    /** @test */
    public function try_from_round_trips_all_cases(): void
    {
        foreach (DemandeStatusEnum::cases() as $case) {
            $this->assertSame($case, DemandeStatusEnum::tryFrom($case->value));
        }
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\BeneficiaireRelationEnum;
use Tests\TestCase;

class BeneficiaireRelationEnumTest extends TestCase
{
    /** @test */
    public function only_family_representatives_are_marked_representative(): void
    {
        $representatives = [
            BeneficiaireRelationEnum::PERE,
            BeneficiaireRelationEnum::MERE,
            BeneficiaireRelationEnum::TUTEUR,
        ];

        foreach (BeneficiaireRelationEnum::cases() as $case) {
            $expected = in_array($case, $representatives, true);
            $this->assertSame($expected, $case->isRepresentative(), $case->value);
        }
    }

    /** @test */
    public function all_cases_have_french_labels(): void
    {
        foreach (BeneficiaireRelationEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
        }
    }
}

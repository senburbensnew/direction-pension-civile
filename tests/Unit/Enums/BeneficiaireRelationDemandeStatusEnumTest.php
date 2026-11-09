<?php

namespace Tests\Unit\Enums;

use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use Tests\TestCase;

class BeneficiaireRelationDemandeStatusEnumTest extends TestCase
{
    /** @test */
    public function only_en_attente_is_pending(): void
    {
        foreach (BeneficiaireRelationDemandeStatusEnum::cases() as $case) {
            $this->assertSame(
                $case === BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE,
                $case->isPending(),
                $case->value
            );
        }
    }

    /** @test */
    public function all_cases_have_labels(): void
    {
        foreach (BeneficiaireRelationDemandeStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
        }
    }
}

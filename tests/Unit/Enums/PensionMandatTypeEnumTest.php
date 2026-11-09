<?php

namespace Tests\Unit\Enums;

use App\Enums\PensionMandatTypeEnum;
use Tests\TestCase;

class PensionMandatTypeEnumTest extends TestCase
{
    /** @test */
    public function only_autre_requires_precision(): void
    {
        foreach (PensionMandatTypeEnum::cases() as $case) {
            $expected = $case === PensionMandatTypeEnum::AUTRE;
            $this->assertSame($expected, $case->requiresPrecision(), $case->value);
        }
    }

    /** @test */
    public function stored_type_persists_autre_precision(): void
    {
        $this->assertSame(
            'procuration',
            PensionMandatTypeEnum::storedType('procuration')
        );
        $this->assertSame(
            'autre',
            PensionMandatTypeEnum::storedType('autre', '   ')
        );
        $this->assertSame(
            'autre:Procuration notariée',
            PensionMandatTypeEnum::storedType('autre', 'Procuration notariée')
        );
        $this->assertSame(
            'autre',
            PensionMandatTypeEnum::storedType('unknown_type')
        );
    }

    /** @test */
    public function display_label_handles_autre_prefix_and_fallback(): void
    {
        $this->assertSame(
            'Autre — Mandat familial',
            PensionMandatTypeEnum::displayLabel('autre:Mandat familial')
        );
        $this->assertSame(
            'Procuration',
            PensionMandatTypeEnum::displayLabel('procuration')
        );
        $this->assertSame(
            'legacy value',
            PensionMandatTypeEnum::displayLabel('legacy_value')
        );
    }

    /** @test */
    public function select_options_exposes_all_cases(): void
    {
        $this->assertSame(PensionMandatTypeEnum::cases(), PensionMandatTypeEnum::selectOptions());
    }
}

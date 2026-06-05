<?php

namespace Tests\Unit\Enums;

use App\Enums\CategorieDossierEnum;
use Tests\TestCase;

class CategorieDossierEnumTest extends TestCase
{
    /** @test */
    public function it_returns_correct_french_labels(): void
    {
        $cases = [
            [CategorieDossierEnum::DEMANDES_PENSION,  'Demandes de pension'],
            [CategorieDossierEnum::DOSSIERS_URGENTS,  'Dossiers urgents'],
            [CategorieDossierEnum::PRESTATIONS,       'Demandes de prestations'],
            [CategorieDossierEnum::ADMINISTRATIF,     'Dossiers administratifs'],
            [CategorieDossierEnum::CORRESPONDANCES,   'Correspondances'],
            [CategorieDossierEnum::DEMANDE_RENCONTRE, 'Demandes de rencontre'],
            [CategorieDossierEnum::AUTRES,            'Autres'],
        ];

        foreach ($cases as [$enum, $expected]) {
            $this->assertSame($expected, $enum->label(), "Label mismatch for {$enum->value}");
        }
    }

    /** @test */
    public function it_returns_correct_badge_classes(): void
    {
        $cases = [
            [CategorieDossierEnum::DEMANDES_PENSION,  'badge-primary'],
            [CategorieDossierEnum::DOSSIERS_URGENTS,  'badge-error'],
            [CategorieDossierEnum::PRESTATIONS,       'badge-info'],
            [CategorieDossierEnum::ADMINISTRATIF,     'badge-warning'],
            [CategorieDossierEnum::CORRESPONDANCES,   'badge-secondary'],
            [CategorieDossierEnum::DEMANDE_RENCONTRE, 'badge-success'],
            [CategorieDossierEnum::AUTRES,            'badge-neutral'],
        ];

        foreach ($cases as [$enum, $expected]) {
            $this->assertSame($expected, $enum->badgeClass(), "Badge class mismatch for {$enum->value}");
        }
    }

    /** @test */
    public function string_values_are_lowercase_slugs(): void
    {
        $this->assertSame('pension', CategorieDossierEnum::DEMANDES_PENSION->value);
        $this->assertSame('urgent', CategorieDossierEnum::DOSSIERS_URGENTS->value);
        $this->assertSame('prestations', CategorieDossierEnum::PRESTATIONS->value);
        $this->assertSame('administratif', CategorieDossierEnum::ADMINISTRATIF->value);
        $this->assertSame('correspondances', CategorieDossierEnum::CORRESPONDANCES->value);
        $this->assertSame('rencontre', CategorieDossierEnum::DEMANDE_RENCONTRE->value);
        $this->assertSame('autres', CategorieDossierEnum::AUTRES->value);
    }

    /** @test */
    public function it_can_be_created_from_string_value(): void
    {
        $enum = CategorieDossierEnum::from('pension');
        $this->assertSame(CategorieDossierEnum::DEMANDES_PENSION, $enum);
    }

    /** @test */
    public function tryFrom_returns_null_for_unknown_value(): void
    {
        $this->assertNull(CategorieDossierEnum::tryFrom('inexistant'));
    }

    /** @test */
    public function all_cases_have_non_empty_label_and_badge(): void
    {
        foreach (CategorieDossierEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), "Empty label for {$case->value}");
            $this->assertNotEmpty($case->badgeClass(), "Empty badge for {$case->value}");
        }
    }
}

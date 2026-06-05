<?php

namespace Tests\Unit\Enums;

use App\Enums\CategorieDossierEnum;
use App\Enums\TypeDemandeEnum;
use Tests\TestCase;

class TypeDemandeEnumTest extends TestCase
{
    /** @test */
    public function it_returns_correct_french_label_for_each_type(): void
    {
        $cases = [
            [TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE, 'Demande de virement bancaire'],
            [TypeDemandeEnum::DEMANDE_ATTESTATION,       "Demande d'attestation"],
            [TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE,  'Demande de transfert de chèque'],
            [TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT,    "Demande d'arrêt de paiement"],
            [TypeDemandeEnum::DEMANDE_REINSERTION,       'Demande de réinsertion'],
            [TypeDemandeEnum::DEMANDE_ARRET_VIREMENT,    "Demande d'arrêt de virement"],
            [TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE,  "Preuve d'existence"],
            [TypeDemandeEnum::DEMANDE_PENSION_REVERSION, 'Demande de pension de réversion'],
            [TypeDemandeEnum::DEMANDE_ETAT_CARRIERE,     "Demande d'état de carrière"],
            [TypeDemandeEnum::DEMANDE_PENSION,           'Demande de pension'],
            [TypeDemandeEnum::DEMANDE_ADHESION,          "Demande d'adhésion"],
            [TypeDemandeEnum::DEMANDE_RENCONTRE,         'Demande de rencontre'],
        ];

        foreach ($cases as [$enum, $expected]) {
            $this->assertSame($expected, $enum->label(), "Label mismatch for {$enum->value}");
        }
    }

    /** @test */
    public function pension_types_map_to_pension_category(): void
    {
        $this->assertSame(CategorieDossierEnum::DEMANDES_PENSION, TypeDemandeEnum::DEMANDE_PENSION->categorie());
        $this->assertSame(CategorieDossierEnum::DEMANDES_PENSION, TypeDemandeEnum::DEMANDE_PENSION_REVERSION->categorie());
    }

    /** @test */
    public function financial_prestations_map_to_prestations_category(): void
    {
        $prestationTypes = [
            TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE,
            TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE,
            TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT,
            TypeDemandeEnum::DEMANDE_ARRET_VIREMENT,
            TypeDemandeEnum::DEMANDE_REINSERTION,
        ];

        foreach ($prestationTypes as $type) {
            $this->assertSame(
                CategorieDossierEnum::PRESTATIONS,
                $type->categorie(),
                "Expected PRESTATIONS for {$type->value}"
            );
        }
    }

    /** @test */
    public function administrative_types_map_to_administratif_category(): void
    {
        $adminTypes = [
            TypeDemandeEnum::DEMANDE_ATTESTATION,
            TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE,
            TypeDemandeEnum::DEMANDE_ETAT_CARRIERE,
        ];

        foreach ($adminTypes as $type) {
            $this->assertSame(
                CategorieDossierEnum::ADMINISTRATIF,
                $type->categorie(),
                "Expected ADMINISTRATIF for {$type->value}"
            );
        }
    }

    /** @test */
    public function adhesion_maps_to_correspondances_category(): void
    {
        $this->assertSame(CategorieDossierEnum::CORRESPONDANCES, TypeDemandeEnum::DEMANDE_ADHESION->categorie());
    }

    /** @test */
    public function rencontre_maps_to_rencontre_category(): void
    {
        $this->assertSame(CategorieDossierEnum::DEMANDE_RENCONTRE, TypeDemandeEnum::DEMANDE_RENCONTRE->categorie());
    }

    /** @test */
    public function all_enum_cases_have_a_category(): void
    {
        foreach (TypeDemandeEnum::cases() as $case) {
            $this->assertInstanceOf(CategorieDossierEnum::class, $case->categorie(), "No category for {$case->value}");
        }
    }

    /** @test */
    public function it_can_be_created_from_string_value(): void
    {
        $enum = TypeDemandeEnum::from('DEMANDE_ATTESTATION');
        $this->assertSame(TypeDemandeEnum::DEMANDE_ATTESTATION, $enum);
    }

    /** @test */
    public function tryFrom_returns_null_for_unknown_value(): void
    {
        $this->assertNull(TypeDemandeEnum::tryFrom('UNKNOWN_TYPE'));
    }
}

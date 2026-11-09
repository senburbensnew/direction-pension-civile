<?php

namespace Tests\Unit\Enums;

use App\Enums\RencontreStatutEnum;
use Tests\TestCase;

class RencontreStatutEnumTest extends TestCase
{
    /** @test */
    public function from_workflow_maps_aliases_and_defaults_to_demande(): void
    {
        $cases = [
            [null, RencontreStatutEnum::DEMANDE],
            ['SOUMISE', RencontreStatutEnum::DEMANDE],
            ['EN_COURS', RencontreStatutEnum::EN_COURS],
            ['ATTRIBUE', RencontreStatutEnum::ATTRIBUE],
            ['EN_ATTENTE', RencontreStatutEnum::ATTRIBUE],
            ['ACTIF', RencontreStatutEnum::ACTIF],
            ['REFUSE', RencontreStatutEnum::REFUSE],
            ['REJETEE', RencontreStatutEnum::REFUSE],
            ['REALISE', RencontreStatutEnum::REALISE],
            ['FINALISEE', RencontreStatutEnum::REALISE],
            ['ANNULE', RencontreStatutEnum::ANNULE],
            ['ANNULEE', RencontreStatutEnum::ANNULE],
            ['REPORTE', RencontreStatutEnum::REPORTE],
            ['NON_HONORE', RencontreStatutEnum::NON_HONORE],
            ['UNKNOWN', RencontreStatutEnum::DEMANDE],
        ];

        foreach ($cases as [$code, $expected]) {
            $this->assertSame($expected, RencontreStatutEnum::fromWorkflow($code));
        }
    }

    /** @test */
    public function terminal_and_slot_reservation_are_consistent(): void
    {
        foreach (RencontreStatutEnum::cases() as $case) {
            if ($case->isTerminal()) {
                $this->assertFalse($case->reservesSlot(), "Terminal status {$case->value} should not reserve a slot");
            }
        }

        $terminalFromCases = array_values(array_map(
            static fn (RencontreStatutEnum $c) => $c->value,
            array_filter(
                RencontreStatutEnum::cases(),
                static fn (RencontreStatutEnum $c) => $c->isTerminal()
            )
        ));

        $this->assertSame(RencontreStatutEnum::terminalValues(), $terminalFromCases);
    }

    /** @test */
    public function rdv_outcome_helpers_match_business_rules(): void
    {
        $this->assertTrue(RencontreStatutEnum::REALISE->isRdvHonore());
        $this->assertTrue(RencontreStatutEnum::REALISE->permetEnregistrementFormalite());
        $this->assertTrue(RencontreStatutEnum::NON_HONORE->isRdvNonHonore());
        $this->assertTrue(RencontreStatutEnum::ANNULE->isRdvAnnule());
        $this->assertTrue(RencontreStatutEnum::REFUSE->isRdvAnnule());
        $this->assertFalse(RencontreStatutEnum::ACTIF->isRdvHonore());
        $this->assertFalse(RencontreStatutEnum::ACTIF->permetEnregistrementFormalite());
    }

    /** @test */
    public function static_outcome_lists_are_stable(): void
    {
        $this->assertSame(
            [RencontreStatutEnum::REALISE, RencontreStatutEnum::NON_HONORE],
            RencontreStatutEnum::agentClosureOutcomes()
        );
        $this->assertSame(
            [RencontreStatutEnum::ANNULE, RencontreStatutEnum::REFUSE],
            RencontreStatutEnum::cancelledOutcomes()
        );
    }

    /** @test */
    public function workflow_code_is_first_matching_step_code(): void
    {
        foreach (RencontreStatutEnum::cases() as $case) {
            $codes = $case->matchingWorkflowStepCodes();
            $this->assertNotEmpty($codes);
            $this->assertSame($codes[0], $case->workflowCode());
        }
    }

    /** @test */
    public function all_cases_have_label_and_badge(): void
    {
        foreach (RencontreStatutEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), "Empty label for {$case->value}");
            $this->assertNotEmpty($case->badgeClass(), "Empty badge for {$case->value}");
        }
    }
}

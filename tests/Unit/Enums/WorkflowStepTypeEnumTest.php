<?php

namespace Tests\Unit\Enums;

use App\Enums\WorkflowStepTypeEnum;
use Tests\TestCase;

class WorkflowStepTypeEnumTest extends TestCase
{
    /** @test */
    public function terminal_and_initial_flags_match_node_types(): void
    {
        $this->assertTrue(WorkflowStepTypeEnum::INITIAL->isInitial());
        $this->assertFalse(WorkflowStepTypeEnum::INITIAL->isTerminal());
        $this->assertTrue(WorkflowStepTypeEnum::TERMINAL->isTerminal());
        $this->assertFalse(WorkflowStepTypeEnum::TERMINAL->isInitial());
    }

    /** @test */
    public function all_cases_have_label_and_badge(): void
    {
        foreach (WorkflowStepTypeEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), $case->value);
            $this->assertNotEmpty($case->badgeClass(), $case->value);
        }
    }
}

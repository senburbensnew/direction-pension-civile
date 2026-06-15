<?php

namespace Tests\Unit\Models;

use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusModelTest extends TestCase
{
    use RefreshDatabase;

    // ─── getStatusStyle ──────────────────────────────────────────────────────

    /** @test */
    public function getStatusStyle_returns_correct_css_classes(): void
    {
        $this->assertStringContainsString('yellow', WorkflowStep::getStatusStyle('EN_ATTENTE'));
        $this->assertStringContainsString('blue',   WorkflowStep::getStatusStyle('APPROUVEE'));
        $this->assertStringContainsString('purple', WorkflowStep::getStatusStyle('EN_COURS'));
        $this->assertStringContainsString('red',    WorkflowStep::getStatusStyle('REJETEE'));
        $this->assertStringContainsString('green',  WorkflowStep::getStatusStyle('FINALISEE'));
        $this->assertStringContainsString('orange', WorkflowStep::getStatusStyle('COMPLEMENT_REQUIS'));
        $this->assertStringContainsString('sky',    WorkflowStep::getStatusStyle('TRANSFERT_EN_ATTENTE'));
    }

    /** @test */
    public function getStatusStyle_returns_gray_for_unknown_code(): void
    {
        $style = WorkflowStep::getStatusStyle('UNKNOWN_STATUS');
        $this->assertStringContainsString('gray', $style);
    }
}

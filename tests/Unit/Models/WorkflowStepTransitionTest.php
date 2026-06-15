<?php

namespace Tests\Unit\Models;

use App\Models\Service;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class WorkflowStepTransitionTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedServices();
    }

    private function direction(): Service
    {
        return Service::where('code', Service::DIRECTION)->first();
    }

    private function liquidation(): Service
    {
        return Service::where('code', Service::LIQUIDATION)->first();
    }

    private function secretariat(): Service
    {
        return Service::where('code', Service::SECRETARIAT)->first();
    }

    private function makeSteps(): array
    {
        $fromStep = WorkflowStep::create(['code' => 'DIR', 'nom' => 'Direction',   'service_id' => $this->direction()->id,   'ordre' => 10]);
        $toStep   = WorkflowStep::create(['code' => 'LIQ', 'nom' => 'Liquidation', 'service_id' => $this->liquidation()->id, 'ordre' => 20]);
        $secStep  = WorkflowStep::create(['code' => 'SEC', 'nom' => 'Secrétariat', 'service_id' => $this->secretariat()->id, 'ordre' => 30]);

        return compact('fromStep', 'toStep', 'secStep');
    }

    // ─── allowed() ───────────────────────────────────────────────────────────

    /** @test */
    public function allowed_returns_true_for_existing_transition(): void
    {
        ['fromStep' => $from, 'toStep' => $to] = $this->makeSteps();

        WorkflowStepTransition::create([
            'from_step_id' => $from->id,
            'to_step_id'   => $to->id,
            'action'       => 'transfer',
            'ordre'        => 1,
        ]);

        $this->assertTrue(WorkflowStepTransition::allowed($from->id, $to->id));
    }

    /** @test */
    public function allowed_returns_false_when_no_matching_transition(): void
    {
        ['fromStep' => $from, 'toStep' => $to] = $this->makeSteps();

        $this->assertFalse(WorkflowStepTransition::allowed($from->id, $to->id));
    }

    /** @test */
    public function allowed_returns_false_for_reversed_transition(): void
    {
        ['fromStep' => $from, 'toStep' => $to] = $this->makeSteps();

        // Only DIR → LIQ configured; LIQ → DIR should be false
        WorkflowStepTransition::create([
            'from_step_id' => $from->id,
            'to_step_id'   => $to->id,
            'action'       => 'transfer',
            'ordre'        => 1,
        ]);

        $this->assertFalse(WorkflowStepTransition::allowed($to->id, $from->id));
    }

    // ─── destinationsFor() ───────────────────────────────────────────────────

    /** @test */
    public function destinationsFor_returns_steps_reachable_from_source(): void
    {
        ['fromStep' => $from, 'toStep' => $to, 'secStep' => $sec] = $this->makeSteps();

        WorkflowStepTransition::create(['from_step_id' => $from->id, 'to_step_id' => $to->id,  'action' => 'transfer', 'ordre' => 1]);
        WorkflowStepTransition::create(['from_step_id' => $from->id, 'to_step_id' => $sec->id, 'action' => 'transfer', 'ordre' => 2]);

        $destinations = WorkflowStepTransition::destinationsFor($from->id);

        $this->assertCount(2, $destinations);
    }

    /** @test */
    public function destinationsFor_returns_empty_when_no_transitions(): void
    {
        ['fromStep' => $from] = $this->makeSteps();

        $destinations = WorkflowStepTransition::destinationsFor($from->id);

        $this->assertCount(0, $destinations);
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    /** @test */
    public function transition_belongs_to_from_step_and_to_step(): void
    {
        ['fromStep' => $from, 'toStep' => $to] = $this->makeSteps();

        $transition = WorkflowStepTransition::create([
            'from_step_id' => $from->id,
            'to_step_id'   => $to->id,
            'action'       => 'transfer',
            'ordre'        => 1,
        ]);

        $this->assertInstanceOf(WorkflowStep::class, $transition->fromStep);
        $this->assertInstanceOf(WorkflowStep::class, $transition->toStep);
        $this->assertEquals($from->id, $transition->fromStep->id);
        $this->assertEquals($to->id, $transition->toStep->id);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\Demande;
use App\Models\DemandeWorkflow;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use App\Enums\TypeDemandeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeWorkflowTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
    }

    private function makeWorkflow(string $receptionStatus = 'pending'): DemandeWorkflow
    {
        $user    = User::factory()->create();
        $service = Service::where('code', Service::DIRECTION)->first();
        $statusId = Status::where('code', 'SOUMISE')->value('id');

        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
        ]);

        return DemandeWorkflow::create([
            'demande_id'        => $demande->id,
            'to_service_id'     => $service->id,
            'status_id'         => $statusId,
            'action_by_user_id' => $user->id,
            'reception_status'  => $receptionStatus,
        ]);
    }

    /** @test */
    public function isPending_returns_true_when_status_is_pending(): void
    {
        $workflow = $this->makeWorkflow('pending');
        $this->assertTrue($workflow->isPending());
        $this->assertFalse($workflow->isAccepted());
        $this->assertFalse($workflow->isRefused());
    }

    /** @test */
    public function isAccepted_returns_true_when_status_is_accepted(): void
    {
        $workflow = $this->makeWorkflow('accepted');
        $this->assertTrue($workflow->isAccepted());
        $this->assertFalse($workflow->isPending());
        $this->assertFalse($workflow->isRefused());
    }

    /** @test */
    public function isRefused_returns_true_when_status_is_refused(): void
    {
        $workflow = $this->makeWorkflow('refused');
        $this->assertTrue($workflow->isRefused());
        $this->assertFalse($workflow->isPending());
        $this->assertFalse($workflow->isAccepted());
    }

    /** @test */
    public function workflow_belongs_to_demande(): void
    {
        $workflow = $this->makeWorkflow();
        $this->assertInstanceOf(Demande::class, $workflow->demande);
    }

    /** @test */
    public function workflow_belongs_to_to_service(): void
    {
        $workflow = $this->makeWorkflow();
        $this->assertInstanceOf(Service::class, $workflow->toService);
    }
}

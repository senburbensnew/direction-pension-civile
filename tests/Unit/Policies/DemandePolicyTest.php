<?php

namespace Tests\Unit\Policies;

use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Policies\DemandePolicy;
use App\Services\Pension\BeneficiaireAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandePolicyTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    private DemandePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->policy = new DemandePolicy;
    }

    /** @test */
    public function access_requires_matching_service(): void
    {
        $service = Service::query()->first();
        $user = User::factory()->create(['service_id' => $service->id]);
        $demande = Demande::create([
            'type' => 'demande_attestation',
            'created_by' => $user->id,
            'current_service_id' => $service->id,
        ]);

        $this->assertTrue($this->policy->access($user, $demande));

        $demande->current_service_id = null;
        $this->assertFalse($this->policy->access($user, $demande));
    }

    /** @test */
    public function view_allows_admin_and_creator(): void
    {
        $creator = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $stranger = User::factory()->create();

        $demande = Demande::create([
            'type' => 'demande_attestation',
            'created_by' => $creator->id,
        ]);

        $this->assertTrue($this->policy->view($admin, $demande));
        $this->assertTrue($this->policy->view($creator, $demande));

        $this->mock(BeneficiaireAccessService::class, function ($mock): void {
            $mock->shouldReceive('userCanViewDemande')->andReturn(false);
        });

        $this->assertFalse($this->policy->view($stranger, $demande));
    }
}

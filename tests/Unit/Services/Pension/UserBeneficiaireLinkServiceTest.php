<?php

namespace Tests\Unit\Services\Pension;

use App\Enums\BeneficiaireRelationEnum;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Services\Pension\UserBeneficiaireLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class UserBeneficiaireLinkServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserBeneficiaireLinkService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserBeneficiaireLinkService;
    }

    /** @test */
    public function it_attaches_user_to_beneficiaire(): void
    {
        $user = User::factory()->create();
        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Adulte',
            'lastname' => 'Test',
            'is_mineur' => false,
        ]);

        $link = $this->service->attach($user, $beneficiaire, BeneficiaireRelationEnum::SELF);

        $this->assertSame($user->id, $link->user_id);
        $this->assertSame(BeneficiaireRelationEnum::SELF, $link->relation);
    }

    /** @test */
    public function it_rejects_self_link_for_mineur(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->attach(
            User::factory()->create(),
            Beneficiaire::create(['firstname' => 'M', 'lastname' => 'T', 'is_mineur' => true]),
            BeneficiaireRelationEnum::SELF
        );
    }
}

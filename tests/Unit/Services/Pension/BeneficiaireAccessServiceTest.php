<?php

namespace Tests\Unit\Services\Pension;

use App\Enums\AccountStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Enums\BeneficiaireRelationEnum;
use App\Services\Pension\BeneficiaireAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaireAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    private BeneficiaireAccessService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BeneficiaireAccessService::class);
    }

    /** @test */
    public function titulaire_can_access_own_beneficiaire(): void
    {
        $user = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-60001');
        $beneficiaire = $user->beneficiaireLinks()->first()->beneficiaire;

        $this->assertTrue($this->service->userCanAccessBeneficiaire($user, $beneficiaire));
        $this->assertTrue(
            $this->service->userCanActOnDemandeType(
                $user,
                $beneficiaire,
                TypeDemandeEnum::DEMANDE_ATTESTATION->value
            )
        );
    }

    /** @test */
    public function user_can_use_pension_code_only_when_affiliated(): void
    {
        $user = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-60002');
        $beneficiaire = $user->beneficiaireLinks()->first()->beneficiaire;

        $this->assertTrue(
            $this->service->userCanUsePensionCodeForDemande(
                $user,
                $beneficiaire,
                '8-60002',
                TypeDemandeEnum::DEMANDE_ATTESTATION->value
            )
        );
        $this->assertFalse(
            $this->service->userCanUsePensionCodeForDemande(
                $user,
                $beneficiaire,
                '8-99999',
                TypeDemandeEnum::DEMANDE_ATTESTATION->value
            )
        );
    }

    /** @test */
    public function stranger_without_link_cannot_access_beneficiaire(): void
    {
        $stranger = User::factory()->create();
        $beneficiaire = Beneficiaire::create([
            'firstname' => 'X',
            'lastname' => 'Y',
            'is_mineur' => false,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-60003',
            'is_primary' => true,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => User::factory()->create()->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::SELF,
        ]);

        $this->assertFalse($this->service->userCanAccessBeneficiaire($stranger, $beneficiaire));
    }
}

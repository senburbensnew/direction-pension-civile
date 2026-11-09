<?php

namespace Tests\Unit\Policies;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Policies\BeneficiairePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiairePolicyTest extends TestCase
{
    use RefreshDatabase;

    private BeneficiairePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(BeneficiairePolicy::class);
    }

    /** @test */
    public function titulaire_can_view_and_deposer_on_own_beneficiaire(): void
    {
        $user = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-40001');
        $beneficiaire = $user->beneficiaireLinks()->first()->beneficiaire;

        $this->assertTrue($this->policy->view($user, $beneficiaire));
        $this->assertTrue($this->policy->deposerDocuments($user, $beneficiaire));
        $this->assertTrue(
            $this->policy->createDemande($user, $beneficiaire, TypeDemandeEnum::DEMANDE_ATTESTATION->value)
        );
    }

    /** @test */
    public function representative_needs_consult_permission_to_view(): void
    {
        [$user, $beneficiaire] = $this->representativeWithPermissions([
            BeneficiaireLinkPermissionEnum::DEPOSER_DOCUMENTS->value,
        ]);

        $this->assertFalse($this->policy->view($user, $beneficiaire));
    }

    /** @test */
    public function representative_with_consult_can_view_but_not_deposer_without_permission(): void
    {
        [$user, $beneficiaire] = $this->representativeWithPermissions([
            BeneficiaireLinkPermissionEnum::CONSULTER_DOSSIER->value,
        ]);

        $this->assertTrue($this->policy->view($user, $beneficiaire));
        $this->assertFalse($this->policy->deposerDocuments($user, $beneficiaire));
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: User, 1: Beneficiaire}
     */
    private function representativeWithPermissions(array $permissions): array
    {
        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Mineur',
            'lastname' => 'Test',
            'is_mineur' => true,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::TUTEUR,
            'status' => BeneficiaireLinkStatusEnum::ACTIVE,
            'permissions' => $permissions,
        ]);

        return [$user, $beneficiaire];
    }
}

<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Services\Pension\ActivePensionContext;
use App\Services\Pension\BeneficiaireProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PensionAffiliationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_have_multiple_pension_codes_on_one_beneficiary(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
        ], '8-11111');

        $beneficiaire = $user->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first()
            ->beneficiaire;

        $service = app(BeneficiaireProvisioningService::class);
        $service->syncAffiliation($beneficiaire, '8-22222', false);

        $this->assertSame(2, $beneficiaire->affiliations()->count());
        $this->assertTrue(AffiliationPension::codeExists('8-22222'));
        $this->assertSame($user->id, User::findByPensionCode('8-11111')?->id);
    }

    /** @test */
    public function active_context_returns_selected_primary_code(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::ACTIF,
        ]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'A',
            'lastname' => 'B',
            'is_mineur' => false,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::SELF,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-99999',
            'is_primary' => true,
        ]);

        $context = ActivePensionContext::for($user);

        $this->assertSame('8-99999', $context->displayPensionCode());
    }

    /** @test */
    public function authenticated_user_can_update_pension_context(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::ACTIF,
        ]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'A',
            'lastname' => 'B',
            'is_mineur' => false,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::SELF,
        ]);

        $a1 = AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-10001',
            'is_primary' => true,
        ]);

        $a2 = AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-10002',
            'is_primary' => false,
        ]);

        $this->actingAs($user)
            ->patch(route('compte.pension-context.update'), [
                'beneficiaire_id' => $beneficiaire->id,
                'affiliation_id' => $a2->id,
            ])
            ->assertRedirect();

        $this->assertSame($a2->id, session(ActivePensionContext::SESSION_AFFILIATION));
    }
}

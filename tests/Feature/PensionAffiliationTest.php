<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\PensionnaireReference;
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
            ->patchJson(route('compte.pension-context.update'), [
                'beneficiaire_id' => $beneficiaire->id,
                'affiliation_id' => $a2->id,
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'display_code' => '8-10002',
            ]);

        $this->assertSame($a2->id, session(ActivePensionContext::SESSION_AFFILIATION));
    }

    /** @test */
    public function user_can_set_existing_affiliation_as_primary_without_overwriting_beneficiaire(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
        ], '8-11111');

        $beneficiaire = $user->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first()
            ->beneficiaire;

        $originalFirstname = $beneficiaire->firstname;

        app(BeneficiaireProvisioningService::class)->syncAffiliation($beneficiaire, '8-22222', false);

        $primary = $beneficiaire->affiliations()->where('code_pension', '8-11111')->first();
        $secondary = $beneficiaire->affiliations()->where('code_pension', '8-22222')->first();

        $this->actingAsConfirmed($user)
            ->patch(route('compte.affiliations.set-primary', $secondary))
            ->assertRedirect();

        $beneficiaire->refresh();
        $secondary->refresh();
        $primary->refresh();

        $this->assertTrue($secondary->is_primary);
        $this->assertFalse($primary->is_primary);
        $this->assertSame($originalFirstname, $beneficiaire->firstname);
        $this->assertSame($secondary->id, session(ActivePensionContext::SESSION_AFFILIATION));
    }

    /** @test */
    public function store_with_existing_code_on_same_beneficiary_can_set_primary(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
        ], '8-33333');

        $beneficiaire = $user->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first()
            ->beneficiaire;

        app(BeneficiaireProvisioningService::class)->syncAffiliation($beneficiaire, '8-44444', false);

        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => '8-44444'],
            ['NOM' => 'TEST', 'PRENOM' => 'USER']
        );

        $this->actingAsConfirmed($user)
            ->post(route('compte.affiliations.store'), [
                'beneficiaire_id' => $beneficiaire->id,
                'code_pension' => '8-44444',
                'loc_description' => 'Code secondaire',
                'is_primary' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $affiliation = AffiliationPension::findByCode('8-44444');

        $this->assertTrue($affiliation->is_primary);
        $this->assertSame(2, $beneficiaire->affiliations()->count());
    }

    /** @test */
    public function primary_affiliation_cannot_be_removed(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
        ], '8-11111');

        $beneficiaire = $user->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first()
            ->beneficiaire;

        app(BeneficiaireProvisioningService::class)->syncAffiliation($beneficiaire, '8-22222', false);

        $primary = $beneficiaire->affiliations()->where('is_primary', true)->first();

        $this->actingAsConfirmed($user)
            ->delete(route('compte.affiliations.destroy', $primary))
            ->assertSessionHasErrors('affiliation');

        $this->assertTrue($primary->fresh()->exists);
        $this->assertSame(2, $beneficiaire->affiliations()->count());
    }

    /** @test */
    public function user_can_add_and_update_pension_code_description(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
        ], '8-11111');

        $beneficiaire = $user->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first()
            ->beneficiaire;

        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => '8-55555'],
            ['NOM' => 'TEST', 'PRENOM' => 'USER']
        );

        $this->actingAsConfirmed($user)
            ->post(route('compte.affiliations.store'), [
                'beneficiaire_id' => $beneficiaire->id,
                'code_pension' => '8-55555',
                'loc_description' => 'Retraite complémentaire',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $affiliation = AffiliationPension::findByCode('8-55555');
        $this->assertSame('Retraite complémentaire', $affiliation->loc_description);

        $this->actingAsConfirmed($user)
            ->patch(route('compte.affiliations.update-description', $affiliation), [
                'loc_description' => 'Mise à jour libellé',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Mise à jour libellé', $affiliation->fresh()->loc_description);
    }

    /** @test */
    public function store_rejects_invalid_pension_code_format(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
        ], '8-11111');

        $beneficiaire = $user->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first()
            ->beneficiaire;

        $this->actingAsConfirmed($user)
            ->post(route('compte.affiliations.store'), [
                'beneficiaire_id' => $beneficiaire->id,
                'code_pension' => 'invalid-code',
                'loc_description' => 'Libellé test',
            ])
            ->assertSessionHasErrors('code_pension');
    }

    /** @test */
    public function store_requires_description(): void
    {
        $user = $this->createPensionneUser([
            'account_status' => AccountStatusEnum::ACTIF,
        ], '8-11111');

        $beneficiaire = $user->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first()
            ->beneficiaire;

        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => '8-66666'],
            ['NOM' => 'TEST', 'PRENOM' => 'USER']
        );

        $this->actingAsConfirmed($user)
            ->post(route('compte.affiliations.store'), [
                'beneficiaire_id' => $beneficiaire->id,
                'code_pension' => '8-66666',
            ])
            ->assertSessionHasErrors('loc_description');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\PensionMandat;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Notifications\PensionMandatApprouveNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PensionMandatWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function mandataire_cannot_use_pension_code_outside_mandat_scope(): void
    {
        $mandataire = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-10000');

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Paul',
            'lastname' => 'Benef',
            'is_mineur' => false,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-20000',
            'is_primary' => true,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $mandataire->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::MANDATAIRE,
        ]);

        PensionMandat::create([
            'user_id' => $mandataire->id,
            'beneficiaire_id' => $beneficiaire->id,
            'type' => 'procuration',
            'status' => PensionMandatStatusEnum::ACTIF,
            'allowed_demande_types' => [TypeDemandeEnum::DEMANDE_ATTESTATION->value],
            'allowed_pension_codes' => ['8-20000'],
        ]);

        session([
            \App\Services\Pension\ActivePensionContext::SESSION_BENEFICIAIRE => $beneficiaire->id,
        ]);

        $context = $mandataire->pensionContext();

        $this->assertTrue($context->userCanUsePensionCodeForDemande('8-20000', TypeDemandeEnum::DEMANDE_ATTESTATION->value));
        $this->assertFalse($context->userCanUsePensionCodeForDemande('8-99999', TypeDemandeEnum::DEMANDE_ATTESTATION->value));
        $this->assertFalse($context->userCanUsePensionCodeForDemande('8-20000', TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value));
    }

    /** @test */
    public function mandat_submission_does_not_create_beneficiaire_link_until_approval(): void
    {
        $mandataire = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-10001');

        $this->actingAs($mandataire)->post(route('compte.mandats.store'), [
            'beneficiaire_firstname' => 'Jean',
            'beneficiaire_lastname' => 'Dupont',
            'code_pension' => '8-10002',
            'type' => 'procuration_consulaire',
            'mandat_document' => \Illuminate\Http\UploadedFile::fake()->create('mandat.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('compte.mandats.index'));

        $this->assertDatabaseHas('pension_mandats', [
            'user_id' => $mandataire->id,
            'status' => PensionMandatStatusEnum::EN_ATTENTE->value,
        ]);

        $this->assertFalse(
            UserBeneficiaireLink::query()
                ->where('user_id', $mandataire->id)
                ->where('relation', BeneficiaireRelationEnum::MANDATAIRE->value)
                ->exists()
        );
    }

    /** @test */
    public function admin_approval_sends_notification_and_persists_scopes(): void
    {
        Notification::fake();
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('pensionne', 'web');

        $admin = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $admin->assignRole('admin');

        $mandataire = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'A',
            'lastname' => 'B',
            'is_mineur' => false,
        ]);

        $mandat = PensionMandat::create([
            'user_id' => $mandataire->id,
            'beneficiaire_id' => $beneficiaire->id,
            'type' => 'procuration',
            'status' => PensionMandatStatusEnum::EN_ATTENTE,
        ]);

        $this->actingAs($admin)->post(route('admin.pension-mandats.approve', $mandat), [
            'allowed_demande_types' => [TypeDemandeEnum::DEMANDE_ATTESTATION->value],
            'allowed_pension_codes' => ['8-55555'],
        ])->assertRedirect();

        $mandat->refresh();
        $this->assertSame(PensionMandatStatusEnum::ACTIF, $mandat->status);
        $this->assertSame([TypeDemandeEnum::DEMANDE_ATTESTATION->value], $mandat->allowed_demande_types);

        Notification::assertSentTo($mandataire, PensionMandatApprouveNotification::class);
    }
}

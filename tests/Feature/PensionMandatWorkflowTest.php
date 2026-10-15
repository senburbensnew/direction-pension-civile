<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Services\Pension\PensionMandatService;
use App\Notifications\PensionMandatApprouveNotification;
use App\Services\Pension\ActivePensionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class PensionMandatWorkflowTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private function makeFormalitesAgent(): User
    {
        $this->seedRoles();
        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $user->assignRole(User::ROLE_AGENT_FORMALITES);

        return $user;
    }

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
            ActivePensionContext::SESSION_BENEFICIAIRE => $beneficiaire->id,
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

        $this->actingAsConfirmed($mandataire)->post(route('compte.mandats.store'), [
            'beneficiaire_firstname' => 'Jean',
            'beneficiaire_lastname' => 'Dupont',
            'code_pension' => '8-10002',
            'type_mandat' => 'procuration_consulaire',
            'date_debut' => now()->subMonth()->toDateString(),
            'date_fin' => now()->addYear()->toDateString(),
            'mandat_document' => UploadedFile::fake()->create('mandat.pdf', 100, 'application/pdf'),
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
    public function formalites_agent_approval_sends_notification_and_persists_scopes(): void
    {
        Notification::fake();
        Role::findOrCreate('pensionne', 'web');

        $agent = $this->makeFormalitesAgent();

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

        $this->actingAs($agent)->post(route('formalites.pension-mandats.approve', $mandat), [
            'allowed_demande_types' => [TypeDemandeEnum::DEMANDE_ATTESTATION->value],
            'allowed_pension_codes' => ['8-55555'],
        ])->assertRedirect();

        $mandat->refresh();
        $this->assertSame(PensionMandatStatusEnum::ACTIF, $mandat->status);
        $this->assertSame([TypeDemandeEnum::DEMANDE_ATTESTATION->value], $mandat->allowed_demande_types);

        Notification::assertSentTo($mandataire, PensionMandatApprouveNotification::class);

        $this->assertDatabaseHas('user_beneficiaire_links', [
            'user_id' => $mandataire->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::MANDATAIRE->value,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_cannot_approve_mandat_via_removed_admin_routes(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $admin->assignRole('admin');

        $mandat = PensionMandat::create([
            'user_id' => User::factory()->create()->id,
            'beneficiaire_id' => Beneficiaire::create([
                'firstname' => 'A',
                'lastname' => 'B',
                'is_mineur' => false,
            ])->id,
            'type' => 'procuration',
            'status' => PensionMandatStatusEnum::EN_ATTENTE,
        ]);

        $this->actingAsConfirmed($admin)
            ->post('/admin/pension-mandats/'.$mandat->id.'/approve', [])
            ->assertNotFound();

        $this->assertSame(PensionMandatStatusEnum::EN_ATTENTE, $mandat->fresh()->status);
    }

    /** @test */
    public function user_cannot_submit_mandat_via_portal_while_dcc_mandataire_pending(): void
    {
        Notification::fake();
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => '8-10003'],
            ['NOM' => 'A', 'PRENOM' => 'B']
        );

        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);

        $demandeCompte = DemandeCreationCompte::create([
            'code' => 'DCC-BLOCK',
            'user_type' => 'externe',
            'pension_code' => '8-10003',
            'telephone' => '+50938101010',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'is_mandataire' => true,
        ]);

        app(PensionMandatService::class)->registerFromDemandeCreationCompte($user, $demandeCompte);

        $this->actingAsConfirmed($user)
            ->from(route('compte.mandats.index'))
            ->post(route('compte.mandats.store'), [
                'beneficiaire_firstname' => 'Jean',
                'beneficiaire_lastname' => 'Dupont',
                'code_pension' => '8-10003',
                'type_mandat' => 'procuration',
                'date_debut' => now()->subMonth()->toDateString(),
                'date_fin' => now()->addYear()->toDateString(),
                'mandat_document' => UploadedFile::fake()->create('mandat.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('code_pension');
    }
}

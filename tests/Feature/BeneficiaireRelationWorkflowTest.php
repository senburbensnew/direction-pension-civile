<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\PensionMandat;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Services\Account\UserCategoryRoleSyncService;
use Database\Seeders\RolesAndPermissionsSeeder;
use App\Services\Pension\ActivePensionContext;
use App\Services\Pension\BeneficiaireAccessService;
use App\Notifications\BeneficiaireRelationApprouveeNotification;
use App\Notifications\BeneficiaireRelationSoumiseNotification;
use App\Services\Pension\BeneficiaireRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class BeneficiaireRelationWorkflowTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflowSteps();
        Notification::fake();
    }

    /** @test */
    public function relation_demande_does_not_grant_access_until_approval(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Enfant',
            'lastname' => 'Test',
            'is_mineur' => true,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-30001',
            'is_primary' => true,
        ]);

        app(BeneficiaireRelationService::class)->submitDemande(
            $user,
            '8-30001',
            BeneficiaireRelationEnum::TUTEUR,
            'Tuteur légal',
            UploadedFile::fake()->create('acte.pdf', 100, 'application/pdf'),
        );

        $this->assertFalse($user->pensionContext()->userCanAccessBeneficiaire($beneficiaire));
    }

    /** @test */
    public function mandat_revocation_removes_beneficiaire_access(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $mandataire = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-30002');

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Paul',
            'lastname' => 'Benef',
            'is_mineur' => false,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-30003',
            'is_primary' => true,
        ]);

        $mandat = PensionMandat::create([
            'user_id' => $mandataire->id,
            'beneficiaire_id' => $beneficiaire->id,
            'type' => 'procuration',
            'status' => PensionMandatStatusEnum::ACTIF,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $mandataire->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::MANDATAIRE,
            'status' => BeneficiaireLinkStatusEnum::ACTIVE,
        ]);

        session([ActivePensionContext::SESSION_BENEFICIAIRE => $beneficiaire->id]);
        $this->assertTrue($mandataire->pensionContext()->userCanAccessBeneficiaire($beneficiaire));

        $agent = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $agent->assignRole(User::ROLE_AGENT_FORMALITES);

        $this->actingAs($agent)->post(route('formalites.pension-mandats.revoke', $mandat))->assertRedirect();

        $mandataire->refresh();
        $this->assertFalse($mandataire->pensionContext()->userCanAccessBeneficiaire($beneficiaire->fresh()));
    }

    /** @test */
    public function agent_formalites_can_access_mandats_index_with_permission(): void
    {
        Permission::findOrCreate('pension_mandats.view', 'web');
        Role::findOrCreate(User::ROLE_AGENT_FORMALITES, 'web')->givePermissionTo('pension_mandats.view');

        $agent = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $agent->assignRole(User::ROLE_AGENT_FORMALITES);

        $this->actingAs($agent)
            ->get(route('formalites.pension-mandats.index'))
            ->assertOk();
    }

    /** @test */
    public function approved_relation_demande_creates_active_link(): void
    {
        Role::findOrCreate('admin', 'web');
        Permission::findOrCreate('beneficiaire_relations.approuver', 'web');

        $reviewer = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $reviewer->assignRole('admin');

        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Mineur',
            'lastname' => 'X',
            'is_mineur' => true,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-30004',
            'is_primary' => true,
        ]);

        $demande = app(BeneficiaireRelationService::class)->submitDemande(
            $user,
            '8-30004',
            BeneficiaireRelationEnum::MERE,
            null,
            UploadedFile::fake()->create('id.pdf', 50, 'application/pdf'),
        );

        app(BeneficiaireRelationService::class)->approve($demande, $reviewer);

        $demande->refresh();
        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::ACCEPTEE, $demande->status);
        $this->assertTrue($user->pensionContext()->userCanAccessBeneficiaire($beneficiaire));
    }

    /** @test */
    public function approve_can_limit_granted_permissions(): void
    {
        Role::findOrCreate('admin', 'web');

        $reviewer = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $reviewer->assignRole('admin');

        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Enfant',
            'lastname' => 'Y',
            'is_mineur' => true,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-30005',
            'is_primary' => true,
        ]);

        $demande = app(BeneficiaireRelationService::class)->submitDemande(
            $user,
            '8-30005',
            BeneficiaireRelationEnum::TUTEUR,
            null,
            UploadedFile::fake()->create('id.pdf', 50, 'application/pdf'),
        );

        $link = app(BeneficiaireRelationService::class)->approve($demande, $reviewer, [
            BeneficiaireLinkPermissionEnum::CONSULTER_DOSSIER->value,
        ]);

        $this->assertSame(
            [BeneficiaireLinkPermissionEnum::CONSULTER_DOSSIER->value],
            $link->permissions,
        );
    }

    /** @test */
    public function externe_representative_can_view_demande_for_linked_beneficiaire_code_only(): void
    {
        $externe = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        app(UserCategoryRoleSyncService::class)->assignCategory($externe, 'externe', primary: true, syncRoles: false);

        $otherUser = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Linked',
            'lastname' => 'Child',
            'is_mineur' => true,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-40001',
            'is_primary' => true,
        ]);

        UserBeneficiaireLink::create([
            'user_id' => $externe->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::MERE,
            'status' => BeneficiaireLinkStatusEnum::ACTIVE,
        ]);

        $access = app(BeneficiaireAccessService::class);
        $type = TypeDemandeEnum::DEMANDE_ATTESTATION->value;

        $this->assertTrue($access->userCanViewDemande($externe, $type, $beneficiaire->id, '8-40001'));
        $this->assertFalse($access->userCanViewDemande($externe, $type, $beneficiaire->id, '8-99999'));
        $this->assertNotEquals($otherUser->id, $externe->id);
    }

    /** @test */
    public function user_can_submit_relation_request_via_http_and_agent_can_approve(): void
    {
        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'fonctionnaire', primary: true);
        $user->assignRole('fonctionnaire');

        $agent = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $agent->assignRole(User::ROLE_AGENT_FORMALITES);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Enfant',
            'lastname' => 'Demo',
            'is_mineur' => true,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-30100',
            'is_primary' => true,
        ]);

        $this->actingAs($user)
            ->post(route('compte.relations.store'), [
                'code_pension' => '8-30100',
                'type_relation' => 'mere',
                'motif' => 'Représentation légale',
                'justificatif' => UploadedFile::fake()->create('acte.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('compte.beneficiaires.index'))
            ->assertSessionHas('success');

        $this->assertFalse($user->pensionContext()->userCanAccessBeneficiaire($beneficiaire));

        Notification::assertSentTo($user, BeneficiaireRelationSoumiseNotification::class);

        $demande = \App\Models\BeneficiaireRelationDemande::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();

        $this->assertNotNull($demande);
        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE, $demande->status);

        $this->actingAs($agent)
            ->post(route('formalites.beneficiaire-relations.approve', $demande))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($user, BeneficiaireRelationApprouveeNotification::class);

        $this->assertTrue($user->fresh()->pensionContext()->userCanAccessBeneficiaire($beneficiaire));
    }

    /** @test */
    public function duplicate_pending_relation_request_is_rejected(): void
    {
        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'fonctionnaire', primary: true);

        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Enfant',
            'lastname' => 'Dup',
            'is_mineur' => true,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => '8-30101',
            'is_primary' => true,
        ]);

        $payload = [
            'code_pension' => '8-30101',
            'type_relation' => 'pere',
            'justificatif' => UploadedFile::fake()->create('acte.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($user)
            ->post(route('compte.relations.store'), $payload)
            ->assertRedirect(route('compte.beneficiaires.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('beneficiaire_relation_demandes', 1);

        try {
            app(BeneficiaireRelationService::class)->submitDemande(
                $user,
                $payload['code_pension'],
                BeneficiaireRelationEnum::PERE,
                null,
                UploadedFile::fake()->create('acte2.pdf', 100, 'application/pdf'),
            );
            $this->fail('Expected duplicate relation demande to be rejected.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('code_pension', $exception->errors());
        }

        $this->assertSame(1, \App\Models\BeneficiaireRelationDemande::query()->count());
    }
}

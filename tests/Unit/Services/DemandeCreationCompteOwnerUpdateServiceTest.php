<?php

namespace Tests\Unit\Services;

use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\PensionMandat;
use App\Models\User;
use App\Services\AccountCreation\DemandeCreationCompteOwnerUpdateService;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\BuildsDemandeCreationCompteFixtures;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeCreationCompteOwnerUpdateServiceTest extends TestCase
{
    use BuildsDemandeCreationCompteFixtures;
    use RefreshDatabase;
    use SeedsRequiredData;

    private DemandeCreationCompteOwnerUpdateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->seed(UserCategoriesSeeder::class);
        $this->service = app(DemandeCreationCompteOwnerUpdateService::class);
    }

    /** @test */
    public function assert_owner_can_edit_rejects_wrong_user(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $intruder = User::factory()->create();

        $this->expectException(HttpException::class);
        $this->service->assertOwnerCanEdit($demande, $intruder);
    }

    /** @test */
    public function assert_owner_can_edit_rejects_non_pending_demandes(): void
    {
        $demande = $this->makePendingTitulaireDemande([
            'status' => DemandeCreationCompte::STATUS_REFUSEE,
        ]);

        try {
            $this->service->assertOwnerCanEdit($demande, $demande->user);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    /** @test */
    public function update_rejects_email_already_used_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $demande = $this->makePendingTitulaireDemande();
        $owner = $demande->user;

        try {
            $this->service->update($demande, $owner, [
                'telephone' => '+50938123456',
                'adresse' => 'Delmas',
                'email' => 'taken@example.com',
            ]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }

    /** @test */
    public function update_titulaire_syncs_demande_and_user_profile(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $owner = $demande->user;

        $updated = $this->service->update($demande, $owner, [
            'firstname' => 'Marie',
            'lastname' => 'Claire',
            'email' => 'marie.claire@example.com',
            'telephone' => '+50938123456',
            'adresse' => 'Delmas 33',
            'nif' => '111-222-333-4',
            'ninu' => '1234567890123',
            'pension_code' => '8-34321',
        ]);

        $owner->refresh();

        $this->assertSame('Marie', $updated->firstname);
        $this->assertSame('marie.claire@example.com', $updated->email);
        $this->assertSame('Marie Claire', $owner->name);
        $this->assertSame('111-222-333-4', $owner->nif);
        $this->assertTrue($updated->histories()->where('event', DemandeCreationCompteHistory::EVENT_MODIFIEE)->exists());
    }

    /** @test */
    public function update_minor_keeps_representative_user_name_but_updates_contact_fields(): void
    {
        $demande = $this->makePendingMinorDemande();
        $owner = $demande->user;
        $originalName = $owner->name;

        $this->service->update($demande, $owner, [
            'firstname' => 'ShouldNot',
            'lastname' => 'OverwriteRep',
            'email' => 'new.rep@example.com',
            'telephone' => '+50939998888',
            'adresse' => 'Petion-Ville',
        ]);

        $owner->refresh();
        $demande->refresh();

        $this->assertSame('new.rep@example.com', $demande->email);
        $this->assertSame('+50939998888', $owner->phone);
        $this->assertSame($originalName, $owner->name);
    }

    /** @test */
    public function update_mandataire_triggers_mandat_resync(): void
    {
        $demande = $this->makePendingMandataireDemande();
        $owner = $demande->user;
        $mandatBefore = PensionMandat::query()->where('demande_creation_compte_id', $demande->id)->first();
        $this->assertNotNull($mandatBefore);

        $this->service->update($demande, $owner, [
            'telephone' => '+50937001122',
            'adresse' => 'Carrefour',
            'pension_code' => '8-34321',
        ]);

        $mandatAfter = PensionMandat::query()->where('demande_creation_compte_id', $demande->id)->first();
        $this->assertNotNull($mandatAfter);
        $this->assertSame($mandatBefore->id, $mandatAfter->id);
    }

    /** @test */
    public function validation_rules_expose_expected_fields(): void
    {
        $rules = DemandeCreationCompteOwnerUpdateService::validationRules();

        $this->assertArrayHasKey('telephone', $rules);
        $this->assertArrayHasKey('adresse', $rules);
        $this->assertArrayHasKey('pension_code', $rules);
        $this->assertSame('required|string|max:500', $rules['adresse']);
    }
}

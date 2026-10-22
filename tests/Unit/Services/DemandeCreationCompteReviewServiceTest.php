<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Models\Beneficiaire;
use App\Models\BeneficiaireRelationDemande;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\PensionMandat;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Notifications\DemandeCreationCompteAccepteeNotification;
use App\Notifications\DemandeCreationCompteRefuseeNotification;
use App\Services\AccountCreation\DemandeCreationCompteReviewService;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\BuildsDemandeCreationCompteFixtures;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeCreationCompteReviewServiceTest extends TestCase
{
    use BuildsDemandeCreationCompteFixtures;
    use RefreshDatabase;
    use SeedsRequiredData;

    private DemandeCreationCompteReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->seedWorkflowSteps();
        $this->seed(UserCategoriesSeeder::class);
        Notification::fake();
        $this->service = app(DemandeCreationCompteReviewService::class);
    }

    /** @test */
    public function accept_rejects_already_processed_demandes(): void
    {
        $demande = $this->makePendingTitulaireDemande([
            'status' => DemandeCreationCompte::STATUS_ACCEPTEE,
        ]);
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        $this->expectException(ValidationException::class);
        $this->service->accept($demande, $reviewer);
    }

    /** @test */
    public function accept_rejects_when_no_formalites_rendez_vous_exists(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $reviewer = $this->makeFormalitesReviewer();

        try {
            $this->service->accept($demande, $reviewer);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    /** @test */
    public function accept_rejects_when_rendez_vous_slot_is_still_in_the_future(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $this->attachFutureRealisedRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        try {
            $this->service->accept($demande, $reviewer);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    /** @test */
    public function accept_rejects_when_provisional_user_was_removed(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $userId = $demande->user_id;
        $this->attachRealisedFormalitesRendezVous($demande);
        User::query()->whereKey($userId)->delete();
        $demande = $demande->fresh();
        $reviewer = $this->makeFormalitesReviewer();

        $this->assertNull($demande->user_id);

        try {
            $this->service->accept($demande, $reviewer);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    /** @test */
    public function accept_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $demande = $this->makePendingTitulaireDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        try {
            $this->service->accept($demande, $reviewer, ['email' => 'taken@example.com']);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }

    /** @test */
    public function accept_activates_titulaire_pensionne_after_realised_rdv(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $userId = $demande->user_id;
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        $result = $this->service->accept($demande, $reviewer, [
            'firstname' => 'Jean',
            'lastname' => 'Pierre',
            'email' => 'jean.pierre@example.com',
        ]);

        $demande->refresh();
        $user = $result['user'];

        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->status);
        $this->assertSame($userId, $demande->user_id);
        $this->assertSame($reviewer->id, $demande->traitee_par);
        $this->assertTrue($user->canAccessFullServices());
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_ACCEPTEE)->exists());
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_COMPTE_ACTIVE)->exists());
        Notification::assertSentTo($user, DemandeCreationCompteAccepteeNotification::class);
    }

    /** @test */
    public function accept_mandataire_scenario_keeps_mandat_pending_for_admin(): void
    {
        $demande = $this->makePendingMandataireDemande();
        $user = $demande->user;
        $mandat = PensionMandat::query()->where('demande_creation_compte_id', $demande->id)->first();
        $this->assertNotNull($mandat);

        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        $this->service->accept($demande, $reviewer, ['email' => 'mandataire@example.com']);

        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->fresh()->status);
        $this->assertTrue($user->fresh()->canAccessFullServices());
        $this->assertSame(PensionMandatStatusEnum::EN_ATTENTE, $mandat->fresh()->status);
        $this->assertFalse(UserBeneficiaireLink::query()->where('user_id', $user->id)->exists());
    }

    /** @test */
    public function accept_minor_scenario_activates_relation_demande_and_link(): void
    {
        $demande = $this->makePendingMinorDemande();
        $user = $demande->user;
        $beneficiaire = Beneficiaire::query()->where('is_mineur', true)->first();
        $this->assertNotNull($beneficiaire);

        $relationDemande = BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demande->id)
            ->first();
        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE, $relationDemande?->status);

        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        $this->service->accept($demande, $reviewer, ['email' => 'mere.rep@example.com']);

        $relationDemande->refresh();
        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::ACCEPTEE, $relationDemande->status);

        $link = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();
        $this->assertSame(BeneficiaireLinkStatusEnum::ACTIVE, $link?->status);
        $this->assertTrue($user->fresh()->pensionContext()->userCanAccessBeneficiaire($beneficiaire));
    }

    /** @test */
    public function refuse_rejects_short_motif(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        try {
            $this->service->refuse($demande, $reviewer, 'nop');
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('refusal_reason', $e->errors());
        }
    }

    /** @test */
    public function refuse_titulaire_blocks_provisional_account(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $userId = $demande->user_id;
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        $this->service->refuse($demande, $reviewer, 'Pièce illisible.');

        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_REFUSEE, $demande->status);
        $this->assertSame('Pièce illisible.', $demande->refusal_reason);
        $this->assertSame(AccountStatusEnum::BLOQUE, $demande->user->account_status);
        $this->assertFalse($demande->user->allowsLogin());
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_REFUSEE)->exists());
        $this->assertTrue($demande->histories()->where('event', DemandeCreationCompteHistory::EVENT_COMPTE_BLOQUE)->exists());
        Notification::assertSentOnDemand(DemandeCreationCompteRefuseeNotification::class);
    }

    /** @test */
    public function refuse_mandataire_refuses_linked_mandat(): void
    {
        $demande = $this->makePendingMandataireDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        $this->service->refuse($demande, $reviewer, 'Mandat non conforme.');

        $mandat = PensionMandat::query()->where('demande_creation_compte_id', $demande->id)->first();
        $this->assertSame(PensionMandatStatusEnum::REFUSE, $mandat?->status);
        $this->assertSame('Mandat non conforme.', $mandat?->refusal_reason);
    }

    /** @test */
    public function refuse_minor_refuses_linked_relation_demande(): void
    {
        $demande = $this->makePendingMinorDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $reviewer = $this->makeFormalitesReviewer();

        $this->service->refuse($demande, $reviewer, 'Dossier mineur incomplet.');

        $relationDemande = BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demande->id)
            ->first();
        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::REFUSEE, $relationDemande?->status);
        $this->assertSame('Dossier mineur incomplet.', $relationDemande?->refusal_reason);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Enums\RencontreStatutEnum;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\User;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\Concerns\BuildsDemandeCreationCompteFixtures;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeCreationCompteTest extends TestCase
{
    use BuildsDemandeCreationCompteFixtures;
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->seedWorkflowSteps();
        $this->seed(UserCategoriesSeeder::class);
        Storage::fake('public');
        Storage::fake(config('uploads.disks.private', 'private'));
    }

    /** @test */
    public function it_reports_pending_status(): void
    {
        $demande = $this->makePendingTitulaireDemande();

        $this->assertTrue($demande->isPending());
        $this->assertSame('En attente', $demande->statusLabel());
        $this->assertStringContainsString('amber', $demande->statusBadgeClass());
    }

    /** @test */
    public function it_labels_accepted_and_refused_statuses(): void
    {
        $accepted = DemandeCreationCompte::make(['status' => DemandeCreationCompte::STATUS_ACCEPTEE]);
        $refused = DemandeCreationCompte::make(['status' => DemandeCreationCompte::STATUS_REFUSEE]);

        $this->assertSame('Acceptée', $accepted->statusLabel());
        $this->assertStringContainsString('green', $accepted->statusBadgeClass());
        $this->assertSame('Refusée', $refused->statusLabel());
        $this->assertStringContainsString('red', $refused->statusBadgeClass());
    }

    /** @test */
    public function display_name_uses_first_and_last_name_then_fallbacks(): void
    {
        $full = DemandeCreationCompte::make([
            'firstname' => 'Jean',
            'lastname' => 'Pierre',
            'name' => 'Legacy',
            'nif' => '123-456-789-0',
        ]);
        $this->assertSame('Jean Pierre', $full->displayName());

        $legacy = DemandeCreationCompte::make([
            'firstname' => '',
            'lastname' => '',
            'name' => 'Legacy Name',
            'nif' => '123-456-789-0',
        ]);
        $this->assertSame('Legacy Name', $legacy->displayName());

        $nifOnly = DemandeCreationCompte::make([
            'firstname' => null,
            'lastname' => null,
            'name' => null,
            'nif' => '123-456-789-0',
        ]);
        $this->assertSame('123-456-789-0', $nifOnly->displayName());

        $empty = DemandeCreationCompte::make([]);
        $this->assertSame('Demandeur', $empty->displayName());
    }

    /** @test */
    public function display_pension_code_trims_and_nullifies_empty(): void
    {
        $this->assertSame('8-34321', DemandeCreationCompte::make(['pension_code' => ' 8-34321 '])->displayPensionCode());
        $this->assertNull(DemandeCreationCompte::make(['pension_code' => ''])->displayPensionCode());
        $this->assertNull(DemandeCreationCompte::make(['pension_code' => null])->displayPensionCode());
    }

    /** @test */
    public function traitee_le_returns_approval_or_rejection_timestamp(): void
    {
        $approved = DemandeCreationCompte::make([
            'approuvee_at' => now()->subHour(),
            'rejetee_at' => null,
        ]);
        $rejected = DemandeCreationCompte::make([
            'approuvee_at' => null,
            'rejetee_at' => now()->subMinutes(30),
        ]);

        $this->assertNotNull($approved->traiteeLe());
        $this->assertNotNull($rejected->traiteeLe());
    }

    /** @test */
    public function pending_digits_match_normalizes_separators_for_pending_demandes_only(): void
    {
        DemandeCreationCompte::create([
            'code' => 'DCC-P1',
            'user_type' => 'pensionne',
            'nif' => '123-456-789-0',
            'telephone' => '+50922000000',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);

        DemandeCreationCompte::create([
            'code' => 'DCC-P2',
            'user_type' => 'pensionne',
            'nif' => '999-888-777-6',
            'telephone' => '+50922000001',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_ACCEPTEE,
        ]);

        $this->assertTrue(DemandeCreationCompte::pendingDigitsMatch('nif', '1234567890'));
        $this->assertFalse(DemandeCreationCompte::pendingDigitsMatch('nif', '9998887776'));
        $this->assertFalse(DemandeCreationCompte::pendingDigitsMatch('nif', ''));
        $this->assertFalse(DemandeCreationCompte::pendingDigitsMatch('invalid_column', '1234567890'));
    }

    /** @test */
    public function pending_telephone_exists_delegates_to_digits_match(): void
    {
        DemandeCreationCompte::create([
            'code' => 'DCC-TEL',
            'user_type' => 'pensionne',
            'telephone' => '50938123456',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);

        $this->assertTrue(DemandeCreationCompte::pendingTelephoneExists('+509 38 12-34-56'));
        $this->assertFalse(DemandeCreationCompte::pendingTelephoneExists(''));
    }

    /** @test */
    public function rendez_vous_helpers_require_linked_user(): void
    {
        $demande = DemandeCreationCompte::create([
            'code' => 'DCC-NOUSER',
            'user_type' => 'pensionne',
            'telephone' => '+50922000000',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
        ]);

        $this->assertNull($demande->rendezVousPourDecisionCompte());
        $this->assertNull($demande->rendezVousPourDecisionCompteRealise());
        $this->assertFalse($demande->hasRendezVousRealise());
    }

    /** @test */
    public function rendez_vous_pour_decision_prefers_non_terminal_appointment(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $this->attachRealisedFormalitesRendezVous($demande);
        $active = $this->attachActiveRendezVous($demande);

        $picked = $demande->fresh()->rendezVousPourDecisionCompte();

        $this->assertNotNull($picked);
        $this->assertSame($active->id, $picked->id);
        $this->assertSame(RencontreStatutEnum::ACTIF, $picked->rencontreStatut());
    }

    /** @test */
    public function has_rendez_vous_realise_and_can_be_decided_after_past_slot(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $this->attachRealisedFormalitesRendezVous($demande);

        $demande = $demande->fresh();

        $this->assertTrue($demande->hasRendezVousRealise());
        $this->assertTrue($demande->canBeDecided());
    }

    /** @test */
    public function can_be_decided_is_false_without_realised_rdv_or_when_already_processed(): void
    {
        $pending = $this->makePendingTitulaireDemande();
        $this->assertFalse($pending->canBeDecided());

        $this->attachFutureRealisedRendezVous($pending);
        $this->assertFalse($pending->fresh()->canBeDecided());

        $accepted = $this->makePendingTitulaireDemande([
            'status' => DemandeCreationCompte::STATUS_ACCEPTEE,
            'nif' => '222-333-444-5',
            'email' => 'accepted.other@example.com',
            'pension_code' => '8-99999',
        ]);
        $this->attachRealisedFormalitesRendezVous($accepted);
        $this->assertFalse($accepted->fresh()->canBeDecided());
    }

    /** @test */
    public function identity_pieces_lists_uploaded_media_with_labels(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $demande->addMedia(UploadedFile::fake()->image('cin.jpg'))
            ->toMediaCollection('identite_cin', 'public');

        $pieces = $demande->fresh()->identityPieces();

        $this->assertCount(1, $pieces);
        $this->assertSame('identite_cin', $pieces[0]['collection']);
        $this->assertSame('Carte d’identification nationale', $pieces[0]['label']);
        $this->assertInstanceOf(Media::class, $pieces[0]['media']);
    }

    /** @test */
    public function agent_piece_url_is_available_only_for_formalites_agent_and_own_media(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $media = $demande->addMedia(UploadedFile::fake()->image('cin.jpg'))
            ->toMediaCollection('identite_cin', 'public');

        $agent = $this->makeFormalitesReviewer();
        $pensionne = $demande->user;

        $this->assertNotNull($demande->agentPieceRouteName($agent));
        $this->assertNull($demande->agentPieceRouteName($pensionne));

        $url = $demande->agentPieceUrl($media, $agent);
        $this->assertNotNull($url);
        $this->assertStringContainsString('inline=1', $demande->agentPieceUrl($media, $agent, true));

        $foreign = Media::make([
            'model_type' => DemandeCreationCompte::class,
            'model_id' => $demande->id + 999,
            'collection_name' => 'identite_cin',
        ]);
        $this->assertNull($demande->agentPieceUrl($foreign, $agent));
    }

    /** @test */
    public function record_history_persists_entry_with_actor(): void
    {
        $demande = $this->makePendingTitulaireDemande();
        $actor = User::factory()->create();

        $history = $demande->recordHistory(
            DemandeCreationCompteHistory::EVENT_MODIFIEE,
            'Test comment',
            $actor,
            DemandeCreationCompte::STATUS_EN_ATTENTE,
            ['foo' => 'bar']
        );

        $this->assertSame(DemandeCreationCompteHistory::EVENT_MODIFIEE, $history->event);
        $this->assertSame($actor->id, $history->changed_by);
        $this->assertTrue($demande->histories()->whereKey($history->id)->exists());
    }

    /** @test */
    public function purge_personal_data_clears_identifiers_and_documents(): void
    {
        $demande = $this->makePendingTitulaireDemande([
            'message' => 'Note',
            'ocr_fields' => ['nom' => 'X'],
        ]);
        $demande->addMedia(UploadedFile::fake()->image('cin.jpg'))
            ->toMediaCollection('identite_cin', 'public');
        $userId = $demande->user_id;

        $demande->purgePersonalData();
        $demande->refresh();

        $this->assertNull($demande->firstname);
        $this->assertNull($demande->email);
        $this->assertNull($demande->user_id);
        $this->assertNull($demande->ocr_fields);
        $this->assertFalse($demande->hasMedia('identite_cin'));
        $this->assertSame($userId, User::find($userId)?->id);
    }
}

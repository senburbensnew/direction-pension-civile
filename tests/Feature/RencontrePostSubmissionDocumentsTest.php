<?php

namespace Tests\Feature;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\Account\UserCategoryRoleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RencontrePostSubmissionDocumentsTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedServices();
        $this->seedWorkflowSteps();
        Storage::fake('public');
    }

    private function makePensionne(array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole('pensionne');
        app(UserCategoryRoleSyncService::class)
            ->assignCategory($user, 'pensionne', primary: true, syncRoles: false);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $dataOverrides
     */
    private function createRencontreDemande(
        User $owner,
        array $dataOverrides = []
    ): Demande {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => $owner->id,
            'current_step_id' => WorkflowStep::idForCode(
                'ACTIF',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            ),
            'data' => array_merge([
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::ACTIF->value,
                'date_souhaitee' => now()->addWeek()->toDateString(),
                'heure_souhaitee' => '14:00',
                'modalite' => 'visio',
            ], $dataOverrides),
        ]);
    }

    public function test_owner_can_upload_documents_after_submission(): void
    {
        $user = $this->makePensionne();
        $demande = $this->createRencontreDemande($user);

        $file = UploadedFile::fake()->create('identite.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('demandes.rencontre.documents', $demande), [
                'doc_piece_identite' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $demande->refresh();
        $this->assertCount(1, $demande->getMedia('piece_identite'));
    }

    public function test_upload_is_forbidden_when_appointment_is_cancelled(): void
    {
        $user = $this->makePensionne();
        $demande = $this->createRencontreDemande($user, [
            'rdv_statut' => RencontreStatutEnum::ANNULE->value,
        ]);

        $file = UploadedFile::fake()->create('identite.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('demandes.rencontre.documents', $demande), [
                'doc_piece_identite' => $file,
            ])
            ->assertForbidden();
    }

    public function test_upload_is_forbidden_when_appointment_is_refused(): void
    {
        $user = $this->makePensionne();
        $demande = $this->createRencontreDemande($user, [
            'rdv_statut' => RencontreStatutEnum::REFUSE->value,
        ]);

        $file = UploadedFile::fake()->create('identite.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('demandes.rencontre.documents', $demande), [
                'doc_piece_identite' => $file,
            ])
            ->assertForbidden();
    }

    public function test_upload_is_still_allowed_when_appointment_is_marked_realise(): void
    {
        $user = $this->makePensionne();
        $demande = $this->createRencontreDemande($user, [
            'rdv_statut' => RencontreStatutEnum::REALISE->value,
        ]);

        $file = UploadedFile::fake()->create('supplement.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('demandes.rencontre.documents', $demande), [
                'doc_supplement' => [$file],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertCount(1, $demande->fresh()->getMedia('rdv_supplement'));
    }

    public function test_other_user_cannot_upload_documents(): void
    {
        $owner = $this->makePensionne();
        $other = $this->makePensionne(['email' => 'other@example.com']);
        $demande = $this->createRencontreDemande($owner);

        $file = UploadedFile::fake()->create('identite.pdf', 100, 'application/pdf');

        $this->actingAs($other)
            ->post(route('demandes.rencontre.documents', $demande), [
                'doc_piece_identite' => $file,
            ])
            ->assertForbidden();
    }

    public function test_multiple_collection_respects_remaining_quota(): void
    {
        $user = $this->makePensionne();
        $demande = $this->createRencontreDemande($user);

        foreach (range(1, 4) as $i) {
            $demande
                ->addMedia(UploadedFile::fake()->create("mandat-{$i}.pdf", 50, 'application/pdf'))
                ->toMediaCollection('pieces_mandat', config('demandes.disk', 'public'));
        }

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.documents', $demande), [
                'doc_pieces_mandat' => [
                    UploadedFile::fake()->create('a.pdf', 50, 'application/pdf'),
                    UploadedFile::fake()->create('b.pdf', 50, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHasErrors('doc_pieces_mandat');
    }

    public function test_empty_submission_is_rejected(): void
    {
        $user = $this->makePensionne();
        $demande = $this->createRencontreDemande($user);

        $this->actingAs($user)
            ->from(route('demandes.rencontre.create'))
            ->post(route('demandes.rencontre.documents', $demande), [])
            ->assertRedirect(route('demandes.rencontre.create'))
            ->assertSessionHasErrors('documents');
    }
}

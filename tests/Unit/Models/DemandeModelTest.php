<?php

namespace Tests\Unit\Models;

use App\Enums\CategorieDossierEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeModelTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
    }

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    // ─── Auto-fill on save ───────────────────────────────────────────────────

    /** @test */
    public function it_auto_sets_brouillon_status_when_none_provided(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
        ]);

        $this->assertEquals('BROUILLON', $demande->status->code);
    }

    /** @test */
    public function it_auto_sets_title_from_type_enum(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
        ]);

        $this->assertEquals(TypeDemandeEnum::DEMANDE_ATTESTATION->label(), $demande->title);
    }

    /** @test */
    public function it_auto_classifies_categorie_from_type(): void
    {
        $user = $this->makeUser();

        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_PENSION->value,
            'created_by' => $user->id,
        ]);

        $this->assertEquals(CategorieDossierEnum::DEMANDES_PENSION->value, $demande->categorie);
    }

    /** @test */
    public function urgent_flag_overrides_type_category_to_dossiers_urgents(): void
    {
        $user = $this->makeUser();

        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'is_urgent'  => true,
        ]);

        $this->assertEquals(CategorieDossierEnum::DOSSIERS_URGENTS->value, $demande->categorie);
    }

    // ─── Status helpers ──────────────────────────────────────────────────────

    /** @test */
    public function isDraft_returns_true_for_brouillon_status(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value, 'created_by' => $user->id]);

        $this->assertTrue($demande->isDraft());
    }

    /** @test */
    public function isSubmitted_returns_true_for_soumise_status(): void
    {
        $user      = $this->makeUser();
        $statusId  = Status::where('code', 'SOUMISE')->value('id');
        $demande   = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'status_id'  => $statusId,
        ]);

        $this->assertTrue($demande->isSubmitted());
        $this->assertFalse($demande->isDraft());
    }

    /** @test */
    public function needsComplement_returns_true_for_complement_requis_status(): void
    {
        $user      = $this->makeUser();
        $statusId  = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
        $demande   = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'status_id'  => $statusId,
        ]);

        $this->assertTrue($demande->needsComplement());
    }

    /** @test */
    public function canBeEditedByUser_is_true_for_brouillon_and_complement_requis(): void
    {
        $user = $this->makeUser();

        $brouillonId    = Status::where('code', 'BROUILLON')->value('id');
        $complementId   = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
        $soumiseId      = Status::where('code', 'SOUMISE')->value('id');

        $draft      = Demande::create(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value, 'created_by' => $user->id, 'status_id' => $brouillonId]);
        $complement = Demande::create(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value, 'created_by' => $user->id, 'status_id' => $complementId]);
        $submitted  = Demande::create(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value, 'created_by' => $user->id, 'status_id' => $soumiseId]);

        $this->assertTrue($draft->canBeEditedByUser());
        $this->assertTrue($complement->canBeEditedByUser());
        $this->assertFalse($submitted->canBeEditedByUser());
    }

    /** @test */
    public function isExpired_returns_true_when_draft_past_expires_at(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'expires_at' => Carbon::yesterday(),
        ]);

        $this->assertTrue($demande->isExpired());
    }

    /** @test */
    public function isExpired_returns_false_when_expires_at_is_future(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'expires_at' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($demande->isExpired());
    }

    // ─── isUrgent ────────────────────────────────────────────────────────────

    /** @test */
    public function isUrgent_returns_true_when_flagged(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by' => $user->id,
            'is_urgent'  => true,
        ]);

        $this->assertTrue($demande->isUrgent());
    }

    /** @test */
    public function isUrgent_returns_true_when_submitted_more_than_30_days_ago(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'         => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by'   => $user->id,
            'submitted_at' => Carbon::now()->subDays(31),
        ]);

        $this->assertTrue($demande->isUrgent());
    }

    /** @test */
    public function isUrgent_returns_false_when_not_flagged_and_recent(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'         => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            'created_by'   => $user->id,
            'is_urgent'    => false,
            'submitted_at' => Carbon::now()->subDays(5),
        ]);

        $this->assertFalse($demande->isUrgent());
    }

    // ─── categorieEnum / categorieLabel ──────────────────────────────────────

    /** @test */
    public function categorieEnum_returns_correct_enum_instance(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_PENSION->value,
            'created_by' => $user->id,
        ]);

        $this->assertSame(CategorieDossierEnum::DEMANDES_PENSION, $demande->categorieEnum());
    }

    /** @test */
    public function categorieLabel_returns_french_label_string(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create([
            'type'       => TypeDemandeEnum::DEMANDE_PENSION->value,
            'created_by' => $user->id,
        ]);

        $this->assertSame('Demandes de pension', $demande->categorieLabel());
    }

    /** @test */
    public function categorieLabel_returns_dash_when_categorie_is_null(): void
    {
        $user = $this->makeUser();
        $demande = new Demande(['created_by' => $user->id]);
        $demande->categorie = null;

        $this->assertSame('—', $demande->categorieLabel());
    }

    // ─── isAnnotated ─────────────────────────────────────────────────────────

    /** @test */
    public function isAnnotated_returns_false_when_not_annotated(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value, 'created_by' => $user->id]);

        $this->assertFalse($demande->isAnnotated());
    }

    /** @test */
    public function isAnnotated_returns_true_after_annotation(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value, 'created_by' => $user->id]);
        $demande->update([
            'annotation'   => 'Note test',
            'annotated_by' => $user->id,
            'annotated_at' => now(),
        ]);

        $this->assertTrue($demande->isAnnotated());
    }

    // ─── hasDocument ─────────────────────────────────────────────────────────

    /** @test */
    public function hasDocument_returns_false_when_no_document_of_type(): void
    {
        $user    = $this->makeUser();
        $demande = Demande::create(['type' => TypeDemandeEnum::DEMANDE_ATTESTATION->value, 'created_by' => $user->id]);

        $this->assertFalse($demande->hasDocument('profile_photo'));
    }
}

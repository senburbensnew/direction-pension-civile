<?php

namespace Tests\Feature;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class RencontreFormalitesWorkflowTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
        $this->seedRoles();
    }

    private function manager(): User
    {
        $user = User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
        ]);
        $user->assignRole(User::ROLE_VALIDATEUR_RDV);

        return $user;
    }

    private function demande(array $data = []): Demande
    {
        return Demande::create([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'created_by' => User::factory()->create()->id,
            'current_service_id' => Service::where('code', Service::FORMALITE)->value('id'),
            'current_step_id' => WorkflowStep::idForCode('SOUMISE'),
            'data' => array_merge([
                'objet' => 'Formalités de pension',
                'modalite' => 'physique',
                'date_souhaitee' => now()->next('Wednesday')->toDateString(),
                'heure_souhaitee' => '14:00',
                'lieu_rdv' => 'Siège DPC',
                'rdv_statut' => RencontreStatutEnum::DEMANDE->value,
                'documents_a_preparer' => ['Carte de pension'],
            ], $data),
        ]);
    }

    public function test_formalites_can_examine_attribute_validate_and_close(): void
    {
        $manager = $this->manager();
        $demande = $this->demande();

        $this->actingAs($manager)
            ->post(route('rencontres.pilotage.examiner', $demande))
            ->assertRedirect(route('rencontres.pilotage.show', $demande));
        $this->assertSame(RencontreStatutEnum::EN_COURS, $demande->fresh()->rencontreStatut());

        $this->actingAs($manager)
            ->post(route('rencontres.pilotage.attribuer', $demande))
            ->assertRedirect(route('rencontres.pilotage.show', $demande));
        $this->assertSame(RencontreStatutEnum::ATTRIBUE, $demande->fresh()->rencontreStatut());

        $this->actingAs($manager)
            ->post(route('rencontres.pilotage.accepter', $demande))
            ->assertRedirect(route('rencontres.pilotage.show', $demande));

        $demande->refresh();
        $this->assertSame(RencontreStatutEnum::VALIDE, $demande->rencontreStatut());
        $this->assertSame($demande->code, $demande->data['confirmation']['numero'] ?? null);
        $this->assertSame('Présentiel', $demande->data['confirmation']['mode'] ?? null);
        $this->assertSame('Siège DPC', $demande->data['confirmation']['lieu'] ?? null);
        $this->assertDatabaseHas('demande_histories', [
            'demande_id' => $demande->id,
            'event' => 'VALIDATED',
        ]);

        $this->actingAs($manager)
            ->post(route('rencontres.pilotage.clore', $demande), [
                'statut' => RencontreStatutEnum::REALISE->value,
                'commentaire' => 'Entretien tenu.',
            ])
            ->assertRedirect(route('rencontres.pilotage.show', $demande));

        $this->assertSame(RencontreStatutEnum::REALISE, $demande->fresh()->rencontreStatut());
        $this->assertDatabaseHas('demande_histories', [
            'demande_id' => $demande->id,
            'event' => 'CLOSED',
            'commentaire' => 'Entretien tenu.',
        ]);
    }

    public function test_manager_can_propose_another_slot(): void
    {
        $manager = $this->manager();
        $demande = $this->demande();
        $date = now()->next('Thursday')->toDateString();

        $this->actingAs($manager)
            ->post(route('rencontres.pilotage.proposer-creneau', $demande), [
                'date_souhaitee' => $date,
                'heure_souhaitee' => '14:30',
            ])
            ->assertRedirect(route('rencontres.pilotage.show', $demande));

        $demande->refresh();
        $this->assertSame($date, $demande->data['date_souhaitee']);
        $this->assertSame('14:30', $demande->data['heure_souhaitee']);
        $this->assertSame(RencontreStatutEnum::ATTRIBUE, $demande->rencontreStatut());
        $this->assertDatabaseHas('demande_histories', [
            'demande_id' => $demande->id,
            'event' => 'MODIFIED',
        ]);
    }
}

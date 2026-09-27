<?php

namespace Tests\Feature;

use App\Enums\TypeDemandeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class DemandeMiseAJourTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedStatuses();
        $this->seedServices();
        Storage::fake('public');
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'Pierre',
            'prenom' => 'Jean',
            'numero_pension' => 'P-100',
            'matricule' => 'M-22',
            'nif' => '123-456-789-0',
            'cinu' => '987-654-321-0',
            'date_naissance' => '1955-03-12',
            'lieu_naissance' => 'Port-au-Prince',
            'adresse' => '12 rue Capois',
            'departement_commune' => 'Ouest / Port-au-Prince',
            'telephone' => '+50938123456',
            'email' => 'jean.pierre@example.com',
            'situation_matrimoniale' => 'celibataire',
            'numero_compte' => '001-234',
            'institution_financiere' => 'BNC',
            'lieu_paiement' => 'Port-au-Prince',
            'situation_pension' => 'reguliere',
            'contact_nom' => 'Marie Pierre',
            'contact_lien' => 'Fille',
            'contact_telephone' => '+50938123457',
            'contact_adresse' => '12 rue Capois',
            'changement' => 'non',
            'certification' => '1',
            'declaration_nom' => 'Jean Pierre',
            'declaration_date' => now()->toDateString(),
            'signature' => 'data:image/png;base64,aaa',
        ], $overrides);
    }

    public function test_pensionne_can_open_the_questionnaire(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pensionne');

        $this->actingAs($user)
            ->get(route('demandes.mise-a-jour.create'))
            ->assertOk()
            ->assertSee('Mise à jour des informations')
            ->assertSee('Identification du pensionné')
            ->assertSee('Je certifie l’exactitude');
    }

    public function test_questionnaire_is_submitted_with_a_reference(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pensionne');

        $this->actingAs($user)
            ->post(route('demandes.mise-a-jour.store'), $this->validPayload())
            ->assertRedirect(route('demandes.mise-a-jour.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('demandes', [
            'type' => TypeDemandeEnum::DEMANDE_MISE_A_JOUR->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_identity_document_is_required_when_a_change_is_declared(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pensionne');

        $this->actingAs($user)
            ->from(route('demandes.mise-a-jour.create'))
            ->post(route('demandes.mise-a-jour.store'), $this->validPayload([
                'changement' => 'oui',
                'changements' => ['adresse'],
            ]))
            ->assertRedirect(route('demandes.mise-a-jour.create'))
            ->assertSessionHasErrors(['piece_identite', 'justificatif_domicile']);
    }

    public function test_change_of_address_can_be_submitted_with_supporting_files(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pensionne');

        $this->actingAs($user)
            ->post(route('demandes.mise-a-jour.store'), $this->validPayload([
                'changement' => 'oui',
                'changements' => ['adresse'],
                'piece_identite' => UploadedFile::fake()->image('cin.jpg'),
                'justificatif_domicile' => UploadedFile::fake()->create('domicile.pdf', 80, 'application/pdf'),
            ]))
            ->assertRedirect(route('demandes.mise-a-jour.create'))
            ->assertSessionHas('success');
    }
}

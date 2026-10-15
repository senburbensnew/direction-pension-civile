<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Beneficiaire;
use App\Models\BeneficiaireRelationDemande;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Models\PensionnaireReference;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Services\HaitiHolidayService;
use App\Services\OCR\OcrService;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

/**
 * Parcours bout en bout : demande de compte → RDV formalités → décision formalités → (mandataire) approbation mandat → agir pour le pensionné.
 */
class CompteCreationParcoursCompletTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private const PASSWORD = 'password123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')->andReturn(
                "NOM : PIERRE\nPRENOM : Jean",
                <<<'TXT'
                REPUBLIQUE D'HAITI
                ACTE DE NAISSANCE
                Nom : MILORME
                Prénom : PIERRE RUBENS
                TXT,
            );
        });

        Storage::fake('public');
        Storage::fake(config('uploads.disks.private', 'private'));

        $this->seedRoles();
        $this->seedServices();
        $this->seedWorkflowSteps();
        $this->seed(UserCategoriesSeeder::class);
        app(HaitiHolidayService::class)->generate(now()->year);
    }

    /** @test */
    public function titulaire_pensionne_parcours_complet_jusqu_a_agir_pour_soi(): void
    {
        $this->seedPensionReference('8-34001', 'PIERRE', 'JEAN');

        $this->logParcours('1/6 — Soumission demande de compte (titulaire)');
        $this->post(route('demandes.compte.store'), $this->payloadTitulaire([
            'nif' => '111-111-111-1',
            'pension_code' => '8-34001',
            'email' => 'titulaire.parcours@example.com',
        ]))->assertRedirect($this->rdvCreateUrl());

        $demande = DemandeCreationCompte::query()->latest('id')->firstOrFail();
        $user = $demande->user;
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($demande->isPending());

        $this->logParcours('2/6 — Prise de rendez-vous formalités');
        $rdv = $this->bookFormalitesRendezVous($user);
        $this->assertSame('demande_formalites_mandats', $rdv->data['motif_key'] ?? null);

        $this->logParcours('3/6 — RDV marqué réalisé (simulation agent)');
        $this->markRendezVousRealise($rdv);
        $this->assertTrue($demande->fresh()->canBeDecided());

        $agent = $this->makeFormalitesAgent();
        $this->logParcours('4/6 — Acceptation dossier par formalités');
        $this->actingAs($agent)
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'firstname' => 'Jean',
                'lastname' => 'Pierre',
                'email' => 'titulaire.parcours@example.com',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $demande));

        $user->refresh();
        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->status);
        $this->assertSame(AccountStatusEnum::ACTIF, $user->account_status);
        $this->assertTrue($user->canAccessFullServices());

        $selfLink = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first();
        $this->assertNotNull($selfLink);
        $this->assertSame(BeneficiaireLinkStatusEnum::ACTIVE, $selfLink->status);

        $this->logParcours('5/6 — Accès espace pension (bénéficiaires)');
        $this->actingAsConfirmed($user)
            ->get(route('compte.beneficiaires.index'))
            ->assertOk();

        $this->logParcours('6/6 — Peut agir pour soi (contexte pension)');
        $this->assertTrue($user->pensionContext()->userCanAccessBeneficiaire($selfLink->beneficiaire));
    }

    /** @test */
    public function representant_mineur_parcours_complet_jusqu_a_agir_pour_le_mineur(): void
    {
        $this->seedPensionReference('8-34002', 'PIERRE', 'JEAN');

        $this->logParcours('1/7 — Soumission DCC représentant d’un mineur');
        $this->post(route('demandes.compte.store'), $this->payloadRepresentantMineur([
            'nif' => '222-222-222-2',
            'pension_code' => '8-34002',
        ]))->assertRedirect($this->rdvCreateUrl());

        $demande = DemandeCreationCompte::query()->latest('id')->firstOrFail();
        $user = $demande->user;
        $beneficiaire = Beneficiaire::query()->where('is_mineur', true)->firstOrFail();
        $this->assertFalse($user->pensionContext()->userCanAccessBeneficiaire($beneficiaire));

        $this->logParcours('2/7 — RDV formalités');
        $rdv = $this->bookFormalitesRendezVous($user);
        $this->markRendezVousRealise($rdv);

        $this->logParcours('3/7 — Acceptation DCC (active la relation mineur)');
        $this->actingAs($this->makeFormalitesAgent())
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'email' => 'rep.mineur.parcours@example.com',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $demande));

        $demande->refresh();
        $this->assertSame(DemandeCreationCompte::STATUS_ACCEPTEE, $demande->status);

        $link = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('relation', 'mere')
            ->firstOrFail();
        $this->assertSame(BeneficiaireLinkStatusEnum::ACTIVE, $link->status);

        $this->logParcours('4/7 — Relation formalités acceptée');
        $relationDemande = BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demande->id)
            ->firstOrFail();
        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::ACCEPTEE, $relationDemande->status);

        $this->logParcours('5/7 — Espace bénéficiaires');
        $this->actingAsConfirmed($user->fresh())
            ->get(route('compte.beneficiaires.index'))
            ->assertOk()
            ->assertSee($beneficiaire->displayName(), false);

        $this->logParcours('6/7 — Accès dossier mineur');
        $this->assertTrue($user->fresh()->pensionContext()->userCanAccessBeneficiaire($beneficiaire));

        $this->logParcours('7/7 — Suivi DCC accessible');
        $this->actingAsConfirmed($user->fresh())
            ->get(route('compte.demande.show'))
            ->assertOk();
    }

    /** @test */
    public function mandataire_parcours_complet_jusqu_a_agir_pour_le_mandant(): void
    {
        $this->seedPensionReference('8-34003', 'LAMOUR', 'ROSE');

        $this->logParcours('1/8 — Soumission DCC mandataire');
        $this->post(route('demandes.compte.store'), $this->payloadMandataire([
            'nif' => '333-333-333-3',
            'pension_code' => '8-34003',
        ]))->assertRedirect($this->rdvCreateUrl());

        $demande = DemandeCreationCompte::query()->latest('id')->firstOrFail();
        $user = $demande->user;
        $mandat = PensionMandat::query()->where('demande_creation_compte_id', $demande->id)->firstOrFail();
        $beneficiaire = $mandat->beneficiaire;
        $this->assertSame(PensionMandatStatusEnum::EN_ATTENTE, $mandat->status);

        $this->logParcours('2/8 — RDV formalités');
        $rdv = $this->bookFormalitesRendezVous($user);
        $this->markRendezVousRealise($rdv);

        $agent = $this->makeFormalitesAgent();
        $this->logParcours('3/8 — Acceptation compte mandataire (mandat reste en attente)');
        $this->actingAs($agent)
            ->post(route('formalites.comptes-demandes.accepter', $demande), [
                'email' => 'mandataire.parcours@example.com',
            ])
            ->assertRedirect(route('formalites.comptes-demandes.show', $demande));

        $mandat->refresh();
        $this->assertSame(PensionMandatStatusEnum::EN_ATTENTE, $mandat->status);
        $this->assertFalse($user->fresh()->pensionContext()->userCanAccessBeneficiaire($beneficiaire));

        $this->logParcours('4/8 — Liste mandats côté formalités');
        $this->actingAs($agent)
            ->get(route('formalites.pension-mandats.index'))
            ->assertOk();

        $this->logParcours('5/8 — Approbation mandat (agents formalités, pas admin)');
        $this->actingAs($agent)
            ->post(route('formalites.pension-mandats.approve', $mandat), [
                'allowed_demande_types' => [TypeDemandeEnum::DEMANDE_ATTESTATION->value],
                'allowed_pension_codes' => ['8-34003'],
            ])
            ->assertRedirect();

        $mandat->refresh();
        $this->assertSame(PensionMandatStatusEnum::ACTIF, $mandat->status);

        $this->logParcours('6/8 — Lien mandataire actif');
        $this->assertDatabaseHas('user_beneficiaire_links', [
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::MANDATAIRE->value,
            'status' => BeneficiaireLinkStatusEnum::ACTIVE->value,
        ]);

        $this->logParcours('7/8 — Peut agir pour le mandant');
        $this->assertTrue($user->fresh()->pensionContext()->userCanAccessBeneficiaire($beneficiaire));

        $this->logParcours('8/8 — Espace mandats / bénéficiaires');
        $this->actingAsConfirmed($user->fresh())
            ->get(route('compte.mandats.index'))
            ->assertOk();
        $this->actingAsConfirmed($user->fresh())
            ->get(route('compte.beneficiaires.index'))
            ->assertOk();
    }

    private function rdvCreateUrl(): string
    {
        return route('demandes.rencontre.create', [
            'motif' => 'demande_formalites_mandats',
        ]);
    }

    private function seedPensionReference(string $code, string $nom, string $prenom): void
    {
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => strtoupper(trim($code))],
            ['NOM' => $nom, 'PRENOM' => $prenom]
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payloadTitulaire(array $overrides = []): array
    {
        return array_merge([
            'user_type' => 'pensionne',
            'telephone' => '+509 22 00 0001',
            'adresse' => '12, rue Capois, Port-au-Prince',
            'piece_identite_type' => 'cin',
            'piece_identite' => UploadedFile::fake()->image('cin.jpg', 800, 600),
            'password' => self::PASSWORD,
            'accept_terms' => '1',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payloadRepresentantMineur(array $overrides = []): array
    {
        return array_merge([
            'user_type' => 'pensionne',
            'telephone' => '+509 38123457',
            'adresse' => 'Delmas 31',
            'is_mineur' => '1',
            'acte_naissance' => UploadedFile::fake()->image('naissance.jpg', 800, 600),
            'representant_lien' => 'mere',
            'piece_identite_representant_type' => 'passeport',
            'piece_identite_representant' => UploadedFile::fake()->image('passeport.jpg', 800, 600),
            'password' => self::PASSWORD,
            'accept_terms' => '1',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payloadMandataire(array $overrides = []): array
    {
        return array_merge([
            'user_type' => 'pensionne',
            'telephone' => '+509 38123458',
            'adresse' => 'Delmas 33',
            'is_mandataire' => '1',
            'piece_identite_type' => 'passeport',
            'piece_identite' => UploadedFile::fake()->image('passeport.jpg', 800, 600),
            'mandat_designation' => UploadedFile::fake()->create('mandat.pdf', 100, 'application/pdf'),
            'password' => self::PASSWORD,
            'accept_terms' => '1',
        ], $overrides);
    }

    private function makeFormalitesRdvAgent(): User
    {
        $agent = User::factory()->create([
            'service_id' => Service::where('code', Service::FORMALITE)->value('id'),
        ]);
        $agent->assignRole(User::ROLE_AGENT_RDV);

        return $agent;
    }

    private function makeFormalitesAgent(): User
    {
        $user = User::factory()->create(['username' => 'formalites.parcours.'.uniqid()]);

        $user->assignRole(User::ROLE_AGENT_FORMALITES);

        return $user;
    }

    private function bookFormalitesRendezVous(User $user): Demande
    {
        $agent = $this->makeFormalitesRdvAgent();

        $this->actingAs($user)
            ->post(route('demandes.rencontre.store'), [
                'motif' => 'demande_formalites_mandats',
                'date_souhaitee' => now()->next('Wednesday')->toDateString(),
                'heure_souhaitee' => '14:30',
                'modalite' => 'visio',
                'lieu_rdv' => 'Siège de la DPC — Port-au-Prince',
                'doc_carte_pension' => UploadedFile::fake()->image('carte.jpg'),
                'doc_piece_identite' => UploadedFile::fake()->image('id.jpg'),
                'doc_matricule_fiscal' => UploadedFile::fake()->image('nif.jpg'),
                'telephone' => '+50938123456',
                'confirmation_lu_accepte' => '1',
            ])
            ->assertRedirect(route('demandes.rencontre.create'));

        return Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where('created_by', $user->id)
            ->latest('id')
            ->firstOrFail();
    }

    private function markRendezVousRealise(Demande $rdv): void
    {
        $rdv->update([
            'data' => array_merge($rdv->fresh()->data ?? [], [
                'motif_key' => 'demande_formalites_mandats',
                'rdv_statut' => RencontreStatutEnum::REALISE->value,
                'date_souhaitee' => now()->subDay()->toDateString(),
                'heure_souhaitee' => '10:00',
            ]),
        ]);
    }

    private function logParcours(string $message): void
    {
        fwrite(STDERR, '[parcours compte] '.$message.PHP_EOL);
    }
}

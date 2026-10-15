<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatusEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Pension\PensionMandatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PensionMandatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => '8-35001'],
            ['NOM' => 'TEST', 'PRENOM' => 'USER']
        );
    }

    /** @test */
    public function register_from_demande_creation_compte_is_idempotent_and_links_dcc(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatusEnum::PROVISOIRE]);
        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'externe', primary: true);

        $demandeCompte = DemandeCreationCompte::create([
            'code' => 'DCC-MAND-1',
            'user_type' => 'externe',
            'pension_code' => '8-35001',
            'telephone' => '+50938000000',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'is_mandataire' => true,
        ]);

        $service = app(PensionMandatService::class);

        $first = $service->registerFromDemandeCreationCompte($user, $demandeCompte);
        $second = $service->registerFromDemandeCreationCompte($user, $demandeCompte->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame($demandeCompte->id, $first->demande_creation_compte_id);
        $this->assertSame(PensionMandatStatusEnum::EN_ATTENTE, $first->status);
        $this->assertDatabaseCount('pension_mandats', 1);
    }

    /** @test */
    public function refuse_from_demande_creation_compte_marks_mandat_refused(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatusEnum::PROVISOIRE]);
        $reviewer = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);

        $demandeCompte = DemandeCreationCompte::create([
            'code' => 'DCC-MAND-2',
            'user_type' => 'externe',
            'pension_code' => '8-35001',
            'telephone' => '+50938000001',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'is_mandataire' => true,
        ]);

        $mandat = app(PensionMandatService::class)->registerFromDemandeCreationCompte($user, $demandeCompte);

        app(PensionMandatService::class)->refuseFromDemandeCreationCompte(
            $demandeCompte,
            $reviewer,
            'Dossier mandataire incomplet.',
        );

        $mandat->refresh();
        $this->assertSame(PensionMandatStatusEnum::REFUSE, $mandat->status);
        $this->assertSame('Dossier mandataire incomplet.', $mandat->refusal_reason);
    }

    /** @test */
    public function assert_can_submit_mandat_blocks_duplicate_pending_via_dcc(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);

        $demandeCompte = DemandeCreationCompte::create([
            'code' => 'DCC-MAND-3',
            'user_type' => 'externe',
            'pension_code' => '8-35001',
            'telephone' => '+50938000002',
            'adresse' => 'PAP',
            'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
            'user_id' => $user->id,
            'is_mandataire' => true,
        ]);

        app(PensionMandatService::class)->registerFromDemandeCreationCompte($user, $demandeCompte);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(PensionMandatService::class)->assertCanSubmitMandat($user, '8-35001');
    }
}

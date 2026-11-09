<?php

namespace Tests\Unit\Services\Pension;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Services\Pension\BeneficiaireRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use App\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class BeneficiaireRelationServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Notification::fake();
        Storage::fake('public');
    }

    /** @test */
    public function submit_demande_rejects_self_and_mandataire_types(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $service = app(BeneficiaireRelationService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->submitDemande($user, '8-70001', BeneficiaireRelationEnum::SELF);
    }

    /** @test */
    public function approve_creates_active_link_and_grants_access(): void
    {
        Role::findOrCreate('admin', 'web');
        $reviewer = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $reviewer->assignRole('admin');

        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $beneficiaire = $this->mineurWithCode('8-70002');

        $service = app(BeneficiaireRelationService::class);
        $demande = $service->submitDemande(
            $user,
            '8-70002',
            BeneficiaireRelationEnum::MERE,
            'Mère du mineur',
            UploadedFile::fake()->create('piece.pdf', 50, 'application/pdf'),
        );

        $this->assertFalse($user->pensionContext()->userCanAccessBeneficiaire($beneficiaire));

        $service->approve($demande, $reviewer);
        $demande->refresh();

        $this->assertSame(BeneficiaireRelationDemandeStatusEnum::ACCEPTEE, $demande->status);
        $this->assertTrue($user->fresh()->pensionContext()->userCanAccessBeneficiaire($beneficiaire));
    }

    /** @test */
    public function approve_can_restrict_permissions_on_link(): void
    {
        Role::findOrCreate('admin', 'web');
        $reviewer = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $reviewer->assignRole('admin');
        $user = User::factory()->create(['account_status' => AccountStatusEnum::ACTIF]);
        $this->mineurWithCode('8-70003');

        $service = app(BeneficiaireRelationService::class);
        $demande = $service->submitDemande(
            $user,
            '8-70003',
            BeneficiaireRelationEnum::TUTEUR,
            null,
            UploadedFile::fake()->create('id.pdf', 50, 'application/pdf'),
        );

        $link = $service->approve($demande, $reviewer, [
            BeneficiaireLinkPermissionEnum::CONSULTER_DOSSIER->value,
        ]);

        $this->assertSame(
            [BeneficiaireLinkPermissionEnum::CONSULTER_DOSSIER->value],
            $link->permissions
        );
    }

    private function mineurWithCode(string $code): Beneficiaire
    {
        $beneficiaire = Beneficiaire::create([
            'firstname' => 'Enfant',
            'lastname' => 'Test',
            'is_mineur' => true,
        ]);

        AffiliationPension::create([
            'beneficiaire_id' => $beneficiaire->id,
            'code_pension' => $code,
            'is_primary' => true,
        ]);

        return $beneficiaire;
    }
}

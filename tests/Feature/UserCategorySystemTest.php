<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Models\Institution;
use App\Models\Service;
use App\Models\User;
use App\Services\Account\InstitutionAccountService;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Pension\BeneficiaireProvisioningService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCategorySystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserCategoriesSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_hold_multiple_categories_with_primary(): void
    {
        $service = Service::query()->create([
            'code' => 'service_test_'.uniqid(),
            'nom' => 'Service test',
        ]);

        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::ACTIF,
            'service_id' => $service->id,
        ]);

        $sync = app(UserCategoryRoleSyncService::class);
        $sync->syncCategories($user, ['pensionne', 'personnel_dpc'], 'personnel_dpc');

        $user->refresh();

        $this->assertTrue($user->hasCategory('pensionne'));
        $this->assertTrue($user->hasCategory('personnel_dpc'));
        $this->assertSame('personnel_dpc', $user->primaryCategoryCode());
        $this->assertTrue($user->hasPensionPortalAccess());
        $this->assertTrue($user->hasAgentPortalAccess());
    }

    public function test_institution_account_is_unique_per_institution(): void
    {
        $service = app(InstitutionAccountService::class);

        $user = $service->createWithLogin(
            ['raison_sociale' => 'Banque Nationale de Crédit'],
            [
                'email' => 'bnc@example.com',
                'password' => 'password123',
                'account_status' => AccountStatusEnum::ACTIF,
            ]
        );

        $this->assertTrue($user->hasCategory('institution'));
        $this->assertNotNull($user->institution_id);
        $this->assertDatabaseCount('institutions', 1);

        $this->expectException(\InvalidArgumentException::class);

        $other = User::factory()->create(['email' => 'other@example.com']);
        $service->ensureInstitutionLink($other, $user->institution);
    }

    public function test_pension_access_without_category_when_beneficiaire_link_exists(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::ACTIF,
        ]);

        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'personnel_dpc', primary: true);
        $user->assignRole('agent_formalites');

        app(BeneficiaireProvisioningService::class)->provisionAdultSelf($user, null, '8-20001');

        $this->assertFalse($user->hasCategory('pensionne'));
        $this->assertTrue($user->fresh()->hasPensionPortalAccess());
    }

    public function test_category_assignment_persists(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::ACTIF,
        ]);

        app(UserCategoryRoleSyncService::class)->assignCategory($user, 'pensionne', primary: true);

        $this->assertDatabaseHas('user_category_assignments', [
            'user_id' => $user->id,
            'user_category_code' => 'pensionne',
            'is_primary' => true,
        ]);
    }
}

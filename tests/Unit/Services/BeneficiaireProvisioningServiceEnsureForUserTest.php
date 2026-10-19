<?php

namespace Tests\Unit\Services;

use App\Enums\BeneficiaireRelationEnum;
use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Pension\BeneficiaireProvisioningService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaireProvisioningServiceEnsureForUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserCategoriesSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_ensure_for_user_returns_null_for_fonctionnaire_without_existing_link(): void
    {
        $user = User::factory()->create();
        app(UserCategoryRoleSyncService::class)->syncSingleCategory($user, 'fonctionnaire');

        $beneficiaire = app(BeneficiaireProvisioningService::class)->ensureForUser($user->fresh());

        $this->assertNull($beneficiaire);
        $this->assertDatabaseCount('user_beneficiaire_links', 0);
    }

    public function test_ensure_for_user_creates_self_link_for_pensionne_without_pension_code(): void
    {
        $user = User::factory()->create([
            'firstname' => 'Marie',
            'lastname' => 'Joseph',
            'nif' => '111222333',
        ]);
        app(UserCategoryRoleSyncService::class)->syncSingleCategory($user, 'pensionne');
        $user->assignRole('pensionne');

        $beneficiaire = app(BeneficiaireProvisioningService::class)->ensureForUser($user->fresh());

        $this->assertNotNull($beneficiaire);
        $this->assertSame('Marie', $beneficiaire->firstname);
        $this->assertSame('Joseph', $beneficiaire->lastname);
        $this->assertDatabaseHas('user_beneficiaire_links', [
            'user_id' => $user->id,
            'beneficiaire_id' => $beneficiaire->id,
            'relation' => BeneficiaireRelationEnum::SELF->value,
        ]);
        $this->assertDatabaseCount('affiliation_pensions', 0);
    }

    public function test_ensure_for_user_is_idempotent_when_self_link_already_exists(): void
    {
        $user = User::factory()->create();
        app(UserCategoryRoleSyncService::class)->syncSingleCategory($user, 'pensionne');
        $user->assignRole('pensionne');

        $service = app(BeneficiaireProvisioningService::class);
        $first = $service->ensureForUser($user->fresh());
        $second = $service->ensureForUser($user->fresh());

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second?->id);
        $this->assertDatabaseCount('user_beneficiaire_links', 1);
        $this->assertDatabaseCount('beneficiaires', 1);
    }
}

<?php

namespace Tests\Unit\Services\Account;

use App\Models\User;
use App\Models\UserCategoryAssignment;
use App\Services\Account\UserCategoryRoleSyncService;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class UserCategoryRoleSyncServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    private UserCategoryRoleSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserCategoriesSeeder::class);
        $this->seedRoles();
        $this->service = app(UserCategoryRoleSyncService::class);
    }

    /** @test */
    public function assign_category_creates_primary_assignment(): void
    {
        $user = User::factory()->create();

        $this->service->assignCategory($user, 'pensionne', primary: true, syncRoles: true);

        $assignment = UserCategoryAssignment::query()
            ->where('user_id', $user->id)
            ->where('user_category_code', 'pensionne')
            ->first();

        $this->assertNotNull($assignment);
        $this->assertTrue($assignment->is_primary);
        $this->assertTrue($user->fresh()->hasRole('pensionne'));
    }

    /** @test */
    public function sync_single_category_replaces_existing_assignments(): void
    {
        $user = User::factory()->create();
        $this->service->syncSingleCategory($user, 'fonctionnaire', syncRoles: true);

        $this->assertSame(
            ['fonctionnaire'],
            UserCategoryAssignment::query()->where('user_id', $user->id)->pluck('user_category_code')->all()
        );
    }
}

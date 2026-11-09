<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\UserCategoryAssignment;
use App\Services\Account\UserCategoryResolver;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class UserCategoryResolverTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserCategoriesSeeder::class);
        $this->seedRoles();
    }

    /** @test */
    public function null_user_has_no_categories(): void
    {
        $resolver = UserCategoryResolver::for(null);

        $this->assertSame([], $resolver->categoryCodes());
        $this->assertNull($resolver->primaryCategoryCode());
        $this->assertFalse($resolver->hasPensionPortalAccess());
    }

    /** @test */
    public function active_assignment_exposes_primary_category(): void
    {
        $user = User::factory()->create();

        UserCategoryAssignment::create([
            'user_id' => $user->id,
            'user_category_code' => 'pensionne',
            'is_primary' => true,
        ]);

        $resolver = UserCategoryResolver::for($user->fresh());

        $this->assertTrue($resolver->hasCategory('pensionne'));
        $this->assertSame('pensionne', $resolver->primaryCategoryCode());
        $this->assertTrue($resolver->hasPensionPortalAccess());
    }

    /** @test */
    public function admin_has_agent_portal_access(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue(UserCategoryResolver::for($admin)->hasAgentPortalAccess());
    }
}

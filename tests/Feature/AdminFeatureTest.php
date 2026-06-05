<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
        $this->seedServices();
        $this->seedRoles();
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    private function makeRegularUser(): User
    {
        return User::factory()->create();
    }

    // ─── Admin dashboard ─────────────────────────────────────────────────────

    /** @test */
    public function admin_can_access_dashboard(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard.index'));

        $response->assertOk();
    }

    /** @test */
    public function regular_user_cannot_access_admin_dashboard(): void
    {
        $user = $this->makeRegularUser();

        $response = $this->actingAs($user)->get(route('admin.dashboard.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard.index'));

        $response->assertRedirect(route('login'));
    }

    // ─── User management ─────────────────────────────────────────────────────

    /** @test */
    public function admin_can_list_users(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
    }

    /** @test */
    public function admin_can_create_user(): void
    {
        $admin   = $this->makeAdmin();
        $service = Service::first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name'                  => 'Nouveau Agent',
            'firstname'             => 'Nouveau',
            'lastname'              => 'Agent',
            'email'                 => 'agent@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role'                  => 'admin',
            'service_id'            => $service->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'agent@example.com',
            'name'  => 'Nouveau Agent',
        ]);
    }

    /** @test */
    public function admin_cannot_create_user_with_duplicate_email(): void
    {
        $admin   = $this->makeAdmin();
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name'                  => 'Dupliqué',
            'email'                 => 'existing@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_can_edit_user(): void
    {
        $admin  = $this->makeAdmin();
        $target = User::factory()->create(['name' => 'Ancien Nom']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name'  => 'Nouveau Nom',
            'email' => $target->email,
        ]);

        $response->assertRedirect(route('admin.users.edit', $target));
        $target->refresh();
        $this->assertEquals('Nouveau Nom', $target->name);
    }

    /** @test */
    public function admin_can_toggle_user_active_status(): void
    {
        $admin  = $this->makeAdmin();
        $target = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $target));

        $response->assertRedirect();
        $target->refresh();
        $this->assertFalse($target->is_active);
    }

    /** @test */
    public function admin_cannot_deactivate_their_own_account(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $admin->refresh();
        $this->assertTrue($admin->is_active);
    }

    /** @test */
    public function admin_can_delete_user(): void
    {
        $admin  = $this->makeAdmin();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $target));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertModelMissing($target);
    }

    /** @test */
    public function regular_user_cannot_delete_users(): void
    {
        $user   = $this->makeRegularUser();
        $target = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('admin.users.destroy', $target));

        $response->assertForbidden();
        $this->assertModelExists($target);
    }

    // ─── Flux transitions ────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_flux_transitions(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.flux-transitions.index'));

        $response->assertOk();
    }

    /** @test */
    public function admin_can_create_flux_transition(): void
    {
        $admin    = $this->makeAdmin();
        $source   = Service::where('code', Service::DIRECTION)->first();
        $dest     = Service::where('code', Service::LIQUIDATION)->first();

        $response = $this->actingAs($admin)->post(route('admin.flux-transitions.store'), [
            'service_source_id'      => $source->id,
            'service_destination_id' => $dest->id,
            'action'                 => 'transfer',
            'ordre'                  => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('flux_transitions', [
            'service_source_id'      => $source->id,
            'service_destination_id' => $dest->id,
        ]);
    }

    /** @test */
    public function admin_can_delete_flux_transition(): void
    {
        $admin    = $this->makeAdmin();
        $source   = Service::where('code', Service::DIRECTION)->first();
        $dest     = Service::where('code', Service::LIQUIDATION)->first();

        $transition = \App\Models\FluxTransition::create([
            'service_source_id'      => $source->id,
            'service_destination_id' => $dest->id,
            'action'                 => 'transfer',
            'ordre'                  => 1,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.flux-transitions.destroy', $transition));

        $response->assertRedirect();
        $this->assertModelMissing($transition);
    }

    /** @test */
    public function regular_user_cannot_manage_flux_transitions(): void
    {
        $user = $this->makeRegularUser();

        $response = $this->actingAs($user)->get(route('admin.flux-transitions.index'));

        $response->assertForbidden();
    }

    // ─── Services list ───────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_services_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.services.index'));

        $response->assertOk();
    }

    // ─── Roles & Permissions ─────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_roles(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.roles.index'));

        $response->assertOk();
    }

    /** @test */
    public function admin_can_view_permissions(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.permissions.index'));

        $response->assertOk();
    }

    // ─── User search / filtering ─────────────────────────────────────────────

    /** @test */
    public function admin_can_search_users_by_name(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['name' => 'Jean Baptiste']);
        User::factory()->create(['name' => 'Marie Claire']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'Jean']));

        $response->assertOk();
        $response->assertSee('Jean Baptiste');
        $response->assertDontSee('Marie Claire');
    }

    /** @test */
    public function admin_can_filter_users_by_role(): void
    {
        $admin   = $this->makeAdmin();
        $dirUser = User::factory()->create(['name' => 'Directeur Test']);
        $dirUser->assignRole('direction');

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'direction']));

        $response->assertOk();
        $response->assertSee('Directeur Test');
    }
}

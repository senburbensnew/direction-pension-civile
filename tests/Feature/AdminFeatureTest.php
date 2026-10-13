<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
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
        $admin = $this->makeAdmin();
        $service = Service::first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Nouveau Agent',
            'firstname' => 'Nouveau',
            'lastname' => 'Agent',
            'email' => 'agent@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role' => 'admin',
            'service_id' => $service->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'agent@example.com',
            'name' => 'Nouveau Agent',
        ]);
    }

    /** @test */
    public function admin_cannot_create_user_with_duplicate_email(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Dupliqué',
            'email' => 'existing@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_can_edit_user(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create(['name' => 'Ancien Nom']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Nouveau Nom',
            'email' => $target->email,
        ]);

        $response->assertRedirect(route('admin.users.edit', $target));
        $target->refresh();
        $this->assertEquals('Nouveau Nom', $target->name);
    }

    /** @test */
    public function admin_can_toggle_user_active_status(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create([
            'account_status' => AccountStatusEnum::ACTIF,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $target));

        $response->assertRedirect();
        $target->refresh();
        $this->assertSame(
            AccountStatusEnum::DESACTIVE,
            $target->account_status
        );
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
        $this->assertTrue($admin->allowsLogin());
    }

    /** @test */
    public function admin_can_delete_user(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $target));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertModelMissing($target);
    }

    /** @test */
    public function regular_user_cannot_delete_users(): void
    {
        $user = $this->makeRegularUser();
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

        $response->assertRedirect(route('admin.flux-transitions.index', [
            'type' => TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value,
        ]));

        $this->actingAs($admin)
            ->get(route('admin.flux-transitions.index', [
                'type' => TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value,
            ]))
            ->assertOk()
            ->assertDontSee('— Commun (global) —', false)
            ->assertSee('Nouveau type')
            ->assertSee('Supprimer');
    }

    /** @test */
    public function admin_can_create_a_demande_type_and_clone_the_base_circuit(): void
    {
        $admin = $this->makeAdmin();
        $source = WorkflowStep::where('code', 'SOUMISE')->whereNull('type_demande')->first();

        $response = $this->actingAs($admin)->post(route('admin.flux-transitions.types.store'), [
            'type_code' => 'DEMANDE_CERTIFICAT',
            'type_label' => 'Demande de certificat',
            'description' => 'Pièce administrative',
            'clone_from' => '__global__',
        ]);

        $response->assertRedirect(route('admin.flux-transitions.index', [
            'type' => 'DEMANDE_CERTIFICAT',
        ]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('types_demandes', [
            'code' => 'DEMANDE_CERTIFICAT',
            'label' => 'Demande de certificat',
        ]);

        $this->assertDatabaseHas('workflow_steps', [
            'code' => 'SOUMISE',
            'type_demande' => 'DEMANDE_CERTIFICAT',
        ]);

        $this->assertNotNull($source);
    }

    /** @test */
    public function admin_can_delete_a_custom_demande_type(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.flux-transitions.types.store'), [
            'type_code' => 'DEMANDE_CERTIFICAT',
            'type_label' => 'Demande de certificat',
            'clone_from' => '__global__',
        ]);

        $this->assertDatabaseHas('types_demandes', ['code' => 'DEMANDE_CERTIFICAT']);
        $this->assertDatabaseHas('workflow_steps', [
            'code' => 'SOUMISE',
            'type_demande' => 'DEMANDE_CERTIFICAT',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.flux-transitions.types.destroy', 'DEMANDE_CERTIFICAT'));

        $response->assertRedirect(route('admin.flux-transitions.index', [
            'type' => TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value,
        ]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('types_demandes', ['code' => 'DEMANDE_CERTIFICAT']);
        $this->assertDatabaseMissing('workflow_steps', [
            'code' => 'SOUMISE',
            'type_demande' => 'DEMANDE_CERTIFICAT',
        ]);
    }

    /** @test */
    public function admin_cannot_delete_a_system_demande_type(): void
    {
        $admin = $this->makeAdmin();
        $code = TypeDemandeEnum::DEMANDE_ATTESTATION->value;

        $response = $this->actingAs($admin)->delete(route('admin.flux-transitions.types.destroy', $code));

        $response->assertRedirect(route('admin.flux-transitions.index', ['type' => $code]));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_create_step_transition(): void
    {
        $admin = $this->makeAdmin();
        $source = Service::where('code', Service::DIRECTION)->first();
        $dest = Service::where('code', Service::LIQUIDATION)->first();

        $fromStep = WorkflowStep::create(['code' => 'DIR',  'nom' => 'Direction',   'service_id' => $source->id, 'ordre' => 10]);
        $toStep = WorkflowStep::create(['code' => 'LIQ',  'nom' => 'Liquidation', 'service_id' => $dest->id,   'ordre' => 20]);

        $response = $this->actingAs($admin)->post(route('admin.flux-transitions.step-transitions.store'), [
            'from_step_id' => $fromStep->id,
            'to_step_id' => $toStep->id,
            'action' => 'transfer',
            'is_urgent_only' => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('workflow_step_transitions', [
            'from_step_id' => $fromStep->id,
            'to_step_id' => $toStep->id,
        ]);
    }

    /** @test */
    public function admin_can_delete_step_transition(): void
    {
        $admin = $this->makeAdmin();
        $source = Service::where('code', Service::DIRECTION)->first();
        $dest = Service::where('code', Service::LIQUIDATION)->first();

        $fromStep = WorkflowStep::create(['code' => 'DIR', 'nom' => 'Direction',   'service_id' => $source->id, 'ordre' => 10]);
        $toStep = WorkflowStep::create(['code' => 'LIQ', 'nom' => 'Liquidation', 'service_id' => $dest->id,   'ordre' => 20]);

        $transition = WorkflowStepTransition::create([
            'from_step_id' => $fromStep->id,
            'to_step_id' => $toStep->id,
            'action' => 'transfer',
            'ordre' => 10,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.flux-transitions.step-transitions.destroy', $transition));

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

    /** @test */
    public function admin_can_create_role_with_permissions(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'custom_support',
            'permissions' => ['CAN_VIEW_DASHBOARD'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role = Role::findByName('custom_support', 'web');
        $this->assertTrue($role->hasPermissionTo('CAN_VIEW_DASHBOARD'));

        $this->actingAs($admin)->get(route('admin.roles.index'))->assertSee('custom_support');
    }

    /** @test */
    public function admin_can_update_role_permissions(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::create(['name' => 'role_to_edit', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'name' => 'role_to_edit',
            'permissions' => ['CAN_VIEW_PENSIONNE_MENU', 'CAN_VIEW_DASHBOARD'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('CAN_VIEW_PENSIONNE_MENU'));
        $this->assertTrue($role->hasPermissionTo('CAN_VIEW_DASHBOARD'));
    }

    /** @test */
    public function admin_cannot_delete_role_with_assigned_users(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::create(['name' => 'role_with_users', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($admin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'role_with_users']);
    }

    /** @test */
    public function admin_can_delete_role_without_users(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::create(['name' => 'empty_role', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['name' => 'empty_role']);
    }

    /** @test */
    public function non_admin_cannot_mutate_roles(): void
    {
        $user = $this->makeRegularUser();
        $role = Role::create(['name' => 'protected_role', 'guard_name' => 'web']);

        $this->actingAs($user)->post(route('admin.roles.store'), [
            'name' => 'hacker_role',
        ])->assertForbidden();

        $this->actingAs($user)->put(route('admin.roles.update', $role), [
            'name' => 'protected_role',
        ])->assertForbidden();

        $this->actingAs($user)->delete(route('admin.roles.destroy', $role))->assertForbidden();
    }

    /** @test */
    public function admin_can_create_permission_via_store_route(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.permissions.store'), [
            'name' => 'CAN_TEST_FEATURE',
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('permissions', ['name' => 'CAN_TEST_FEATURE']);
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
        $admin = $this->makeAdmin();
        $dirUser = User::factory()->create(['name' => 'Directeur Test']);
        $dirUser->assignRole('directeur');

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'directeur']));

        $response->assertOk();
        $response->assertSee('Directeur Test');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Mail\AdminEmailTestMail;
use App\Models\Service;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\SmsService;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflowSteps();
        $this->seedServices();
        $this->seedRoles();
        $this->seedGenders();
        $this->seed(UserCategoriesSeeder::class);
    }

    /** @return array<string, mixed> */
    private function identityFields(array $overrides = []): array
    {
        return array_merge([
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'gender_id' => $this->defaultGenderId(),
        ], $overrides);
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
    public function admin_can_access_email_test_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.email.test'))
            ->assertOk()
            ->assertSee('Test d’envoi d’e-mail', false);
    }

    /** @test */
    public function admin_can_access_personal_notifications_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Mes notifications', false);
    }

    /** @test */
    public function admin_can_send_email_test(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.email.test.store'), [
                'to' => 'test@example.com',
                'subject' => 'Test SMTP',
                'body' => 'Corps du message.',
            ])
            ->assertRedirect(route('admin.email.test'))
            ->assertSessionHas('success');

        Mail::assertQueued(AdminEmailTestMail::class, function (AdminEmailTestMail $mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function admin_can_access_sms_test_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.sms.test'))
            ->assertOk()
            ->assertSee('Test d’envoi SMS', false);
    }

    /** @test */
    public function admin_can_send_sms_test(): void
    {
        $this->mock(SmsService::class, function ($mock): void {
            $mock->shouldReceive('sendTest')
                ->once()
                ->with('+50938123456', 'Corps du SMS.');
        });

        $admin = $this->makeAdmin();

        config([
            'services.twilio.account_sid' => 'AC'.str_repeat('a', 32),
            'services.twilio.auth_token' => str_repeat('b', 32),
            'services.twilio.from' => '+15551234567',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sms.test.store'), [
                'to' => '+50938123456',
                'body' => 'Corps du SMS.',
            ])
            ->assertRedirect(route('admin.sms.test'))
            ->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_access_queue_monitor(): void
    {
        config(['queue.default' => 'database']);

        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.queue.index'))
            ->assertOk()
            ->assertSee('Jobs mail / SMS / notifications', false);
    }

    /** @test */
    public function admin_queue_monitor_lists_pending_jobs(): void
    {
        config(['queue.default' => 'database']);

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\SendSmsJob']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.queue.index'))
            ->assertOk()
            ->assertSee('SendSmsJob', false)
            ->assertSee('En attente', false);
    }

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
    public function creating_agent_rdv_without_categories_does_not_add_agent_rdv_user_category(): void
    {
        $this->seed(UserCategoriesSeeder::class);

        $admin = $this->makeAdmin();
        $service = Service::first();

        UserCategory::query()->create([
            'code' => 'agent_rdv',
            'label' => 'agent_rdv',
            'is_system' => false,
        ]);

        $createView = $this->actingAs($admin)->get(route('admin.users.create'));
        $createView->assertOk();
        $this->assertStringNotContainsString('Catégories cumulables', $createView->getContent());

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Agent RDV Test',
            'firstname' => 'Agent',
            'lastname' => 'RDV',
            'email' => 'agent-rdv@example.com',
            'phone' => '+50938123456',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => [User::ROLE_AGENT_RDV],
            'service_id' => $service->id,
        ]));

        $response->assertRedirect(route('admin.users.index'));

        $this->assertSame(
            1,
            UserCategory::query()->where('code', 'agent_rdv')->count(),
            'La création ne doit pas ajouter une catégorie agent_rdv.'
        );

        $created = User::query()->where('email', 'agent-rdv@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole(User::ROLE_AGENT_RDV));
        $this->assertTrue($created->hasCategory('personnel_dpc'));
        $this->assertDatabaseMissing('user_category_assignments', [
            'user_id' => $created->id,
            'user_category_code' => 'agent_rdv',
        ]);
    }

    /** @test */
    public function admin_can_create_user(): void
    {
        $admin = $this->makeAdmin();
        $service = Service::first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Nouveau Agent',
            'firstname' => 'Nouveau',
            'lastname' => 'Agent',
            'email' => 'agent@example.com',
            'phone' => '+50938123456',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['admin'],
            'service_id' => $service->id,
        ]));

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

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Dupliqué',
            'email' => 'existing@example.com',
            'phone' => '+50938123456',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]));

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_can_edit_user(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create(['name' => 'Ancien Nom']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), $this->identityFields([
            'name' => 'Nouveau Nom',
            'email' => $target->email,
            'phone' => '+50938123456',
        ]));

        $response->assertRedirect(route('admin.users.edit', $target));
        $target->refresh();
        $this->assertEquals('Nouveau Nom', $target->name);
    }

    /** @test */
    public function admin_user_forms_expose_dual_listbox_for_roles(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('data-dual-listbox', false)
            ->assertSee('Rôles disponibles', false);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $target))
            ->assertOk()
            ->assertSee('data-dual-listbox', false)
            ->assertSee('Rôles attribués', false);
    }

    /** @test */
    public function admin_can_create_user_with_multiple_roles_via_roles_array(): void
    {
        $admin = $this->makeAdmin();
        $service = Service::where('code', 'service_accueil_formalites')->first()
            ?? Service::first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Agent Formalités',
            'firstname' => 'Agent',
            'lastname' => 'Formalités',
            'email' => 'multi-roles@example.com',
            'phone' => '+50938123456',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => [User::ROLE_AGENT_FORMALITES, User::ROLE_AGENT_RDV],
            'service_id' => $service->id,
            'primary_category' => 'personnel_dpc',
        ]));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasNoErrors();

        $created = User::query()->where('email', 'multi-roles@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasCategory('personnel_dpc'));
        $this->assertFalse($created->hasRole('fonctionnaire'));
        $this->assertTrue($created->hasRole(User::ROLE_AGENT_FORMALITES));
        $this->assertTrue($created->hasRole(User::ROLE_AGENT_RDV));
    }

    /** @test */
    public function admin_can_update_user_roles_via_roles_array(): void
    {
        $admin = $this->makeAdmin();
        $service = Service::where('code', 'service_accueil_formalites')->first()
            ?? Service::first();

        $target = User::factory()->create([
            'service_id' => $service->id,
        ]);
        $target->syncRoles([User::ROLE_AGENT_RDV]);
        app(UserCategoryRoleSyncService::class)->syncSingleCategory($target, 'personnel_dpc', syncRoles: false);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), $this->identityFields([
            'name' => $target->name,
            'email' => $target->email,
            'phone' => '+50938123456',
            'service_id' => $service->id,
            'primary_category' => 'personnel_dpc',
            'roles' => [User::ROLE_AGENT_FORMALITES],
        ]));

        $response->assertRedirect(route('admin.users.edit', $target));
        $response->assertSessionHasNoErrors();

        $target->refresh();
        $this->assertFalse($target->hasRole('fonctionnaire'));
        $this->assertTrue($target->hasRole(User::ROLE_AGENT_FORMALITES));
        $this->assertFalse($target->hasRole(User::ROLE_AGENT_RDV));
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
    public function admin_cannot_delete_user(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)
            ->delete("/admin/users/{$target->id}");

        $response->assertMethodNotAllowed();
        $this->assertModelExists($target);
    }

    /** @test */
    public function regular_user_cannot_delete_users(): void
    {
        $user = $this->makeRegularUser();
        $target = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/admin/users/{$target->id}");

        $response->assertMethodNotAllowed();
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
            'permissions' => ['can_view_dashboard'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role = Role::findByName('custom_support', 'web');
        $this->assertTrue($role->hasPermissionTo('can_view_dashboard'));

        $this->actingAs($admin)->get(route('admin.roles.index'))->assertSee('custom_support');
    }

    /** @test */
    public function admin_can_update_role_permissions(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::create(['name' => 'role_to_edit', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'name' => 'role_to_edit',
            'permissions' => ['can_view_pensionne_menu', 'can_view_dashboard'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('can_view_pensionne_menu'));
        $this->assertTrue($role->hasPermissionTo('can_view_dashboard'));
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
            'name' => 'can_test_feature',
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('permissions', ['name' => 'can_test_feature']);
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

<?php

namespace Tests\Feature\Auth;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Registration is admin-only in this application.
 * The /register route requires auth + role:admin.
 * Admins create accounts on behalf of employees / pensionnes.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        foreach (RolesAndPermissionsSeeder::canonicalRoles() as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        $this->seed(UserCategoriesSeeder::class);
    }

    /** @test */
    public function guest_cannot_access_register_form(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function regular_authenticated_user_cannot_access_register_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_view_registration_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/register');

        $response->assertOk();
    }

    /** @test */
    public function admin_can_create_new_fonctionnaire_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/register', [
            'name' => 'Dupont Jean',
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'nif' => '123-456-789-0',
            'primary_category' => UserTypeEnum::FONCTIONNAIRE->value,
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'jean.dupont@example.com',
            'name' => 'Dupont Jean',
        ]);

        $user = User::where('email', 'jean.dupont@example.com')->first();
        $this->assertTrue($user->hasCategory('fonctionnaire'));
    }

    /** @test */
    public function admin_cannot_register_duplicate_email(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post('/register', [
            'name' => 'Autre Nom',
            'firstname' => 'Autre',
            'lastname' => 'Nom',
            'email' => 'existing@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'nif' => '111-111-111-1',
            'primary_category' => UserTypeEnum::FONCTIONNAIRE->value,
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_cannot_register_user_without_required_fields(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/register', []);

        $response->assertSessionHasErrors(['name', 'firstname', 'lastname', 'email', 'password', 'nif', 'primary_category']);
    }

    /** @test */
    public function registering_new_user_does_not_change_authenticated_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post('/register', [
            'name' => 'Nouveau Fonctionnaire',
            'firstname' => 'Nouveau',
            'lastname' => 'Fonctionnaire',
            'email' => 'nouveau@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'nif' => '222-222-222-2',
            'primary_category' => UserTypeEnum::FONCTIONNAIRE->value,
        ]);

        $this->assertAuthenticatedAs($admin);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Registration is admin-only in this application.
 * The /register route requires auth + role:admin.
 * Admins create accounts on behalf of employees / pensionnaires.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        foreach (['admin', 'direction', 'pensionnaire', 'fonctionnaire', 'institution', 'secretariat', 'liquidation'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
        }
    }

    private function seedUserTypes(): array
    {
        return array_map(fn($name) => UserType::create(['name' => $name]), [
            UserTypeEnum::PENSIONNAIRE->value,
            UserTypeEnum::FONCTIONNAIRE->value,
            UserTypeEnum::INSTITUTION->value,
        ]);
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

        $this->seedUserTypes();

        $response = $this->actingAs($admin)->get('/register');

        $response->assertOk();
    }

    /** @test */
    public function admin_can_create_new_fonctionnaire_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $types      = $this->seedUserTypes();
        $typeId     = UserType::where('name', UserTypeEnum::FONCTIONNAIRE->value)->value('id');

        $response = $this->actingAs($admin)->post('/register', [
            'name'                  => 'Dupont Jean',
            'firstname'             => 'Jean',
            'lastname'              => 'Dupont',
            'email'                 => 'jean.dupont@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'nif'                   => '123-456-789-0',
            'user_type_id'          => $typeId,
        ]);

        $response->assertRedirect(route('admin.dashboard.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'jean.dupont@example.com',
            'name'  => 'Dupont Jean',
        ]);
    }

    /** @test */
    public function admin_cannot_register_duplicate_email(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $types  = $this->seedUserTypes();
        $typeId = UserType::where('name', UserTypeEnum::FONCTIONNAIRE->value)->value('id');

        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post('/register', [
            'name'                  => 'Autre Nom',
            'firstname'             => 'Autre',
            'lastname'              => 'Nom',
            'email'                 => 'existing@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'nif'                   => '111-111-111-1',
            'user_type_id'          => $typeId,
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_cannot_register_user_without_required_fields(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/register', []);

        $response->assertSessionHasErrors(['name', 'firstname', 'lastname', 'email', 'password', 'nif', 'user_type_id']);
    }

    /** @test */
    public function registering_new_user_does_not_change_authenticated_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $types  = $this->seedUserTypes();
        $typeId = UserType::where('name', UserTypeEnum::FONCTIONNAIRE->value)->value('id');

        $this->actingAs($admin)->post('/register', [
            'name'                  => 'Nouveau Fonctionnaire',
            'firstname'             => 'Nouveau',
            'lastname'              => 'Fonctionnaire',
            'email'                 => 'nouveau@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'nif'                   => '222-222-222-2',
            'user_type_id'          => $typeId,
        ]);

        // Admin is still the one authenticated, not the newly created user
        $this->assertAuthenticatedAs($admin);
    }
}

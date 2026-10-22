<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatusEnum;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_login_always_redirects_home_instead_of_a_role_restricted_page(): void
    {
        $user = User::factory()->create();

        $this->get('/register');

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->get(RouteServiceProvider::HOME)->assertOk()->assertDontSee('403');
    }

    public function test_users_can_authenticate_using_their_nif(): void
    {
        $user = User::factory()->create(['nif' => '123-456-789-0']);

        $response = $this->post('/login', [
            'login' => '1234567890',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_authenticate_using_their_pension_code(): void
    {
        $user = $this->createPensionneUser([], '8-34321');

        $response = $this->post('/login', [
            'login' => '8-34321',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_authenticate_using_their_phone(): void
    {
        $user = User::factory()->create(['phone' => '+50938123456']);

        $response = $this->post('/login', [
            'login' => '+50938123456',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_cannot_authenticate_with_phone_missing_international_prefix(): void
    {
        User::factory()->create(['phone' => '+50938123456']);

        $this->post('/login', [
            'login' => '50938123456',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /** Username is not an accepted login identifier (phone, email, NIF, pension code only). */
    public function test_users_cannot_authenticate_using_their_username(): void
    {
        $user = User::factory()->create(['username' => 'jean.pierre']);

        $this->post('/login', [
            'login' => 'jean.pierre',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_authenticate_using_their_email_in_the_login_field(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => strtoupper($user->email),
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_with_desactive_account_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatusEnum::DESACTIVE,
        ]);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_with_remember_me_sets_remember_token(): void
    {
        $user = User::factory()->create(['remember_token' => null]);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_login_without_remember_me_does_not_set_remember_token(): void
    {
        $user = User::factory()->create(['remember_token' => null]);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('_token');
    }
}

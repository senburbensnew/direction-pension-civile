<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequiredPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_required_password_change_when_flag_is_set(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('password.required'));
    }

    public function test_user_with_required_change_cannot_access_profile_until_password_is_updated(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('password.required'));

        $this->actingAs($user)
            ->from(route('password.required'))
            ->put(route('password.required.update'), [
                'current_password' => 'password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(RouteServiceProvider::HOME);

        $user->refresh();

        $this->assertFalse($user->mustChangePassword());
        $this->assertTrue(Hash::check('new-secure-password', $user->password));

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk();
    }
}

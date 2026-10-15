<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
        $response->assertSee(__('guest.confirm_password.title'), false);
    }

    public function test_sensitive_action_redirects_to_confirm_password_with_reauth_message(): void
    {
        $user = User::factory()->create([
            'phone' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'phone' => '+50937001234',
            ]);

        $response->assertRedirect(route('password.confirm'));
        $response->assertSessionHas('password_reauth_required', true);

        $followUp = $this->get(route('password.confirm'));
        $followUp->assertSee(__('guest.confirm_password.reauth_required'), false);
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAsConfirmed($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create([
            'phone' => null,
        ]);

        $response = $this
            ->actingAsConfirmed($user)
            ->patch('/profile', [
                'phone' => '+50937001234',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('+50937001234', $user->phone);
    }

    public function test_filled_profile_fields_cannot_be_changed_via_profile_form(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
            'phone' => '+50937009999',
        ]);

        $response = $this
            ->actingAsConfirmed($user)
            ->patch('/profile', [
                'email' => 'new@example.com',
                'phone' => '+50937001111',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('locked@example.com', $user->email);
        $this->assertSame('+50937001111', $user->phone);
    }

    public function test_empty_personal_fields_can_be_completed(): void
    {
        $user = User::factory()->create([
            'firstname' => null,
            'lastname' => null,
            'address' => null,
        ]);

        $response = $this
            ->actingAsConfirmed($user)
            ->patch('/profile', [
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
                'address' => 'Port-au-Prince',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Jean', $user->firstname);
        $this->assertSame('Dupont', $user->lastname);
        $this->assertSame('Jean Dupont', $user->name);
        $this->assertSame('Port-au-Prince', $user->address);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAsConfirmed($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAsConfirmed($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAsConfirmed($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}

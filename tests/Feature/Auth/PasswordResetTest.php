<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_code_screen_can_be_rendered(): void
    {
        $response = $this->get('/reset-password-code');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['login' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_link_can_be_requested_with_legacy_email_field(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_sends_sms_code_when_only_phone_on_file(): void
    {
        Notification::fake();
        Config::set('notifications.sms_enabled', true);

        $user = User::factory()->create([
            'email' => null,
            'phone' => '+50938123456',
        ]);

        $this->mock(SmsService::class, function ($mock): void {
            $mock->shouldReceive('send')->once();
        });

        $this->post('/forgot-password', ['login' => '+50938123456'])
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_reset_password_sends_email_and_sms_when_both_on_file(): void
    {
        Notification::fake();
        Config::set('notifications.sms_enabled', true);

        $user = User::factory()->create(['phone' => '+50938123456']);

        $this->mock(SmsService::class, function ($mock): void {
            $mock->shouldReceive('send')->once();
        });

        $this->post('/forgot-password', ['login' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_link_can_be_requested_with_nif_via_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['nif' => '1234567890']);

        $this->post('/forgot-password', ['login' => '123-456-789-0'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_link_cannot_be_requested_with_username(): void
    {
        Notification::fake();

        User::factory()->create(['username' => 'jean.pierre']);

        $this->post('/forgot-password', ['login' => 'jean.pierre']);

        Notification::assertNothingSent();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['login' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['login' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_sms_code(): void
    {
        $user = User::factory()->create(['phone' => '+50938123456']);

        cache()->put(
            'password_reset_sms:'.$user->id,
            Hash::make('123456'),
            now()->addMinutes(15)
        );

        $this->post('/reset-password-code', [
            'login' => $user->email,
            'code' => '123456',
            'password' => 'new-password-9',
            'password_confirmation' => 'new-password-9',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $user->refresh();

        $this->assertTrue(Hash::check('new-password-9', $user->password));
    }
}

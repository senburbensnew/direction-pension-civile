<?php

namespace Tests\Feature\Auth;

use App\Mail\LoginTwoFactorCodeMail;
use App\Models\Service;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\Account\UserCategoryRoleSyncService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PersonnelLoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserCategoriesSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_personnel_dpc_with_email_requires_two_factor_by_email(): void
    {
        Mail::fake();

        $user = $this->createPersonnelUser(['email' => 'agent@example.com']);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login.two-factor'));

        Mail::assertQueued(LoginTwoFactorCodeMail::class, function (LoginTwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return strlen($code) === 6;
        });

        $response = $this->post(route('login.two-factor.store'), ['code' => $code ?? '000000']);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_admin_without_personnel_category_requires_two_factor_by_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login.two-factor'));

        $code = null;
        Mail::assertQueued(LoginTwoFactorCodeMail::class, function (LoginTwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return strlen($code) === 6;
        });

        $this->post(route('login.two-factor.store'), ['code' => $code])
            ->assertRedirect(route('admin.dashboard.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_pensionne_login_skips_two_factor(): void
    {
        Mail::fake();

        $user = $this->createPensionneUser();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
        Mail::assertNothingSent();
    }

    public function test_expired_password_applies_only_to_personnel_dpc(): void
    {
        config(['auth.password_max_age_days' => 90]);

        $personnel = $this->createPersonnelUser([
            'must_change_password' => false,
            'password_changed_at' => now()->subDays(120),
        ]);

        $pensionne = $this->createPensionneUser([
            'must_change_password' => false,
            'password_changed_at' => now()->subDays(120),
        ]);

        $this->assertTrue($personnel->fresh()->mustChangePassword());
        $this->assertFalse($pensionne->fresh()->mustChangePassword());
    }

    public function test_personnel_with_expired_password_redirects_after_two_factor(): void
    {
        Mail::fake();
        config(['auth.password_max_age_days' => 90]);

        $user = $this->createPersonnelUser([
            'must_change_password' => false,
            'password_changed_at' => now()->subDays(120),
        ]);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login.two-factor'));

        $code = null;
        Mail::assertQueued(LoginTwoFactorCodeMail::class, function (LoginTwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->post(route('login.two-factor.store'), ['code' => $code])
            ->assertRedirect(route('password.required'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPersonnelUser(array $attributes = []): User
    {
        $service = Service::query()->create([
            'code' => 'service_test_'.uniqid(),
            'nom' => 'Service test',
        ]);

        $user = User::factory()->create(array_merge([
            'service_id' => $service->id,
            'must_change_password' => false,
            'password_changed_at' => now(),
        ], $attributes));

        app(UserCategoryRoleSyncService::class)->syncSingleCategory($user, 'personnel_dpc', syncRoles: false);

        return $user->fresh();
    }
}

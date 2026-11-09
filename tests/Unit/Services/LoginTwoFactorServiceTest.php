<?php

namespace Tests\Unit\Services;

use App\Enums\LoginTwoFactorChannelEnum;
use App\Models\User;
use App\Services\LoginTwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class LoginTwoFactorServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRequiredData;

    private LoginTwoFactorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Config::set('auth.two_factor.enabled', true);
        $this->service = app(LoginTwoFactorService::class);
    }

    /** @test */
    public function pensionne_does_not_require_two_factor_challenge(): void
    {
        $user = User::factory()->create(['email' => 'pensionne@example.com']);
        $user->assignRole('pensionne');

        $this->assertNull($this->service->channelFor($user));
        $this->assertFalse($this->service->requiresChallenge($user));
    }

    /** @test */
    public function admin_with_email_uses_email_channel(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('admin');

        $this->assertSame(LoginTwoFactorChannelEnum::Email, $this->service->channelFor($admin));
        $this->assertTrue($this->service->requiresChallenge($admin));
    }

    /** @test */
    public function two_factor_disabled_returns_no_channel(): void
    {
        Config::set('auth.two_factor.enabled', false);

        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('admin');

        $this->assertNull($this->service->channelFor($admin));
    }
}

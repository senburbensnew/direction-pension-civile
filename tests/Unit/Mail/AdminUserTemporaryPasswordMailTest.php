<?php

namespace Tests\Unit\Mail;

use App\Mail\AdminUserTemporaryPasswordMail;
use App\Models\User;
use Tests\TestCase;

class AdminUserTemporaryPasswordMailTest extends TestCase
{
    /** @test */
    public function content_exposes_temporary_password_and_login_url(): void
    {
        $user = User::factory()->make(['email' => 'agent@example.com']);
        $initiator = User::factory()->make();

        $mailable = new AdminUserTemporaryPasswordMail($user, 'Temp#123', $initiator);

        $this->assertStringContainsString('mot de passe', $mailable->envelope()->subject);
        $this->assertSame('Temp#123', $mailable->content()->with['temporaryPassword']);
        $this->assertSame(route('login'), $mailable->content()->with['loginUrl']);
    }
}

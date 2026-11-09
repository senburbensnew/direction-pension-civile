<?php

namespace Tests\Unit\Mail;

use App\Mail\LoginTwoFactorCodeMail;
use Tests\TestCase;

class LoginTwoFactorCodeMailTest extends TestCase
{
    /** @test */
    public function content_includes_verification_code(): void
    {
        $mailable = new LoginTwoFactorCodeMail('654321');

        $this->assertSame('654321', $mailable->content()->with['code']);
        $this->assertSame('mail.login-two-factor-code', $mailable->content()->markdown);
    }
}

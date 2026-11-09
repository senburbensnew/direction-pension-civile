<?php

namespace Tests\Unit\Support;

use App\Support\LoginTwoFactorSession;
use Tests\TestCase;

class LoginTwoFactorSessionTest extends TestCase
{
    /** @test */
    public function session_keys_are_stable(): void
    {
        $this->assertSame('login.two_factor.user_id', LoginTwoFactorSession::USER_ID);
        $this->assertSame('login.two_factor.remember', LoginTwoFactorSession::REMEMBER);
        $this->assertSame('login.two_factor.channel', LoginTwoFactorSession::CHANNEL);
        $this->assertSame('login.two_factor.login_identifier', LoginTwoFactorSession::LOGIN_IDENTIFIER);
    }
}

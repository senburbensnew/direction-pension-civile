<?php

namespace Tests\Unit\Enums;

use App\Enums\LoginTwoFactorChannelEnum;
use Tests\TestCase;

class LoginTwoFactorChannelEnumTest extends TestCase
{
    /** @test */
    public function it_supports_email_and_sms_channels(): void
    {
        $this->assertSame('email', LoginTwoFactorChannelEnum::Email->value);
        $this->assertSame('sms', LoginTwoFactorChannelEnum::Sms->value);
        $this->assertCount(2, LoginTwoFactorChannelEnum::cases());
    }
}

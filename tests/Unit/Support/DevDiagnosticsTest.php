<?php

namespace Tests\Unit\Support;

use App\Support\DevDiagnostics;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DevDiagnosticsTest extends TestCase
{
    public function test_otp_logging_requires_local_and_dev_log_otp(): void
    {
        Config::set('dev.log_otp', false);
        $this->assertFalse(DevDiagnostics::shouldLogOtp());

        Config::set('dev.log_otp', true);
        $this->assertFalse(DevDiagnostics::shouldLogOtp());

        $this->app['env'] = 'local';
        $this->assertTrue(DevDiagnostics::shouldLogOtp());
    }

    public function test_mail_logging_requires_local_and_dev_log_mail(): void
    {
        Config::set('dev.log_mail', false);
        $this->assertFalse(DevDiagnostics::shouldLogMail());

        Config::set('dev.log_mail', true);
        $this->assertFalse(DevDiagnostics::shouldLogMail());

        $this->app['env'] = 'local';
        $this->assertTrue(DevDiagnostics::shouldLogMail());
    }

    public function test_mail_logging_skipped_when_using_mail_mailer(): void
    {
        Config::set('mail.default', 'mail');
        Config::set('dev.log_mail', true);
        $this->app['env'] = 'local';

        $this->assertFalse(DevDiagnostics::shouldLogMail());
    }
}

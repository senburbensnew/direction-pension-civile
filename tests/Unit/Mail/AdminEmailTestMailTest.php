<?php

namespace Tests\Unit\Mail;

use App\Mail\AdminEmailTestMail;
use Tests\TestCase;

class AdminEmailTestMailTest extends TestCase
{
    /** @test */
    public function envelope_and_content_use_constructor_values(): void
    {
        $mailable = new AdminEmailTestMail('Corps du test', 'Sujet test');

        $this->assertSame('Sujet test', $mailable->envelope()->subject);
        $this->assertSame('mail.admin-email-test', $mailable->content()->text);
        $this->assertSame('Corps du test', $mailable->content()->with['bodyText']);
    }
}

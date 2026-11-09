<?php

namespace Tests\Unit\Support;

use App\Support\Newsletter\NewsletterHtmlSanitizer;
use Tests\TestCase;

class NewsletterHtmlSanitizerTest extends TestCase
{
    /** @test */
    public function it_strips_disallowed_tags_and_scripts(): void
    {
        $dirty = '<p>Hello</p><script>alert(1)</script><strong>OK</strong>';

        $this->assertSame('<p>Hello</p>alert(1)<strong>OK</strong>', NewsletterHtmlSanitizer::sanitize($dirty));
        $this->assertStringNotContainsString('<script', NewsletterHtmlSanitizer::sanitize($dirty) ?? '');
    }

    /** @test */
    public function blank_html_returns_null(): void
    {
        $this->assertNull(NewsletterHtmlSanitizer::sanitize('<br>'));
        $this->assertNull(NewsletterHtmlSanitizer::sanitize(null));
    }
}

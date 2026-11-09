<?php

namespace Tests\Unit\Support;

use App\Support\Newsletter\NewsletterEmailNormalizer;
use Tests\TestCase;

class NewsletterEmailNormalizerTest extends TestCase
{
    /** @test */
    public function it_trims_and_lowercases(): void
    {
        $this->assertSame(
            'user@example.com',
            NewsletterEmailNormalizer::normalize('  User@Example.COM  ')
        );
    }

    /** @test */
    public function empty_input_returns_empty_string(): void
    {
        $this->assertSame('', NewsletterEmailNormalizer::normalize('   '));
    }
}

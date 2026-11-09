<?php

namespace Tests\Unit\Rules;

use App\Rules\Base64Image;
use Tests\Support\ValidatesValidationRule;
use Tests\TestCase;

class Base64ImageRuleTest extends TestCase
{
    use ValidatesValidationRule;

    /** 1×1 PNG */
    private const TINY_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';

    /** @test */
    public function it_accepts_small_valid_data_uri_png(): void
    {
        $this->assertRulePasses(new Base64Image, self::TINY_PNG);
    }

    /** @test */
    public function it_rejects_non_data_uri_or_wrong_mime(): void
    {
        $this->assertRuleFails(new Base64Image, 'not-an-image');
        $this->assertRuleFails(new Base64Image, 'data:text/plain;base64,YQ==');
    }

    /** @test */
    public function it_rejects_invalid_base64_payload(): void
    {
        $this->assertRuleFails(new Base64Image, 'data:image/png;base64,!!!');
    }
}

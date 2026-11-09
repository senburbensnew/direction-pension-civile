<?php

namespace Tests\Unit\Services;

use App\Services\Pension\PensionCodeResolver;
use Illuminate\Http\Request;
use Tests\TestCase;

class PensionCodeResolverTest extends TestCase
{
    /** @test */
    public function from_request_normalizes_first_filled_candidate(): void
    {
        $request = Request::create('/', 'POST', [
            'pension_code' => ' 8-34321 ',
            'code_pension' => '7-ABCDE',
        ]);

        $this->assertSame('7-ABCDE', PensionCodeResolver::fromRequest($request));
    }

    /** @test */
    public function from_request_reads_nested_data_code(): void
    {
        $request = Request::create('/', 'POST', [
            'data' => ['code_pension' => '8-99999'],
        ]);

        $this->assertSame('8-99999', PensionCodeResolver::fromRequest($request));
    }

    /** @test */
    public function from_validated_returns_null_when_missing(): void
    {
        $this->assertNull(PensionCodeResolver::fromValidated([]));
        $this->assertSame('8-11111', PensionCodeResolver::fromValidated(['numero_pension' => '8-11111']));
    }
}

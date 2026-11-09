<?php

namespace Tests\Unit\Helpers;

use App\Helpers\CodeGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CodeGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_unique_prefixed_codes(): void
    {
        Schema::create('tmp_codes', function ($table) {
            $table->id();
            $table->string('code')->unique();
        });

        DB::table('tmp_codes')->insert(['code' => 'REQ-'.now()->format('Ymd').'-000001']);

        $code = CodeGeneratorService::generateUniqueRequestCode('REQ', 'tmp_codes');

        $this->assertStringStartsWith('REQ-'.now()->format('Ymd').'-', $code);
        $this->assertNotSame('REQ-'.now()->format('Ymd').'-000001', $code);
        $this->assertFalse(DB::table('tmp_codes')->where('code', $code)->exists());
    }
}

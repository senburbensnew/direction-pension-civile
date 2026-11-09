<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helpers;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    /** 1×1 PNG */
    private const TINY_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';

    /** @test */
    public function cleanup_files_deletes_paths_on_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tmp/a.txt', 'x');
        Storage::disk('public')->put('tmp/b.txt', 'y');

        Helpers::cleanupFiles(['tmp/a.txt', 'tmp/b.txt']);

        $this->assertFalse(Storage::disk('public')->exists('tmp/a.txt'));
        $this->assertFalse(Storage::disk('public')->exists('tmp/b.txt'));
    }

    /** @test */
    public function process_base64_image_stores_png_under_sanitized_folder(): void
    {
        Storage::fake('public');

        $path = Helpers::processBase64Image(
            self::TINY_PNG,
            '/uploads/../proofs',
            'Échec enregistrement'
        );

        $this->assertStringStartsWith('uploads/proofs/', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    /** @test */
    public function process_base64_image_rejects_invalid_format(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Échec enregistrement');

        Helpers::processBase64Image('not-an-image', 'folder', 'Échec enregistrement');
    }
}

<?php

namespace Tests\Unit\Support;

use App\Support\Storage\UploadPaths;
use Tests\TestCase;

class UploadPathsTest extends TestCase
{
    /** @test */
    public function disk_helpers_read_from_config(): void
    {
        $this->assertSame(config('uploads.disks.public', 'public'), UploadPaths::publicDisk());
        $this->assertSame(config('uploads.disks.private', 'private'), UploadPaths::privateDisk());
    }

    /** @test */
    public function cms_and_report_paths_compose_expected_segments(): void
    {
        $this->assertSame(
            config('uploads.paths.cms.mediatheque').'/image',
            UploadPaths::mediatheque('image')
        );
        $this->assertStringEndsWith('/2024', UploadPaths::reportYear('2024'));
        $this->assertStringContainsString('unspecified', UploadPaths::reportYear(null));
    }

    /** @test */
    public function demande_embedded_path_substitutes_type_and_date(): void
    {
        $path = UploadPaths::demandeEmbeddedPath('Demande Attestation');

        $this->assertStringContainsString('demande-attestation', $path);
        $this->assertStringContainsString(now()->format('Y'), $path);
        $this->assertStringContainsString(now()->format('m'), $path);
    }
}

<?php

namespace Tests\Unit\Support;

use App\Support\Storage\LegacyPublicStorageMigrator;
use App\Support\Storage\UploadPaths;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyPublicStorageMigratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function migrate_dry_run_completes_with_zero_moves_when_tree_empty(): void
    {
        Storage::fake(UploadPaths::publicDisk());

        $moved = (new LegacyPublicStorageMigrator)->migrate(dryRun: true);

        $this->assertSame(0, $moved);
    }
}

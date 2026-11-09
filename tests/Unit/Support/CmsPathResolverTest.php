<?php

namespace Tests\Unit\Support;

use App\Support\Storage\CmsPathResolver;
use Tests\TestCase;

class CmsPathResolverTest extends TestCase
{
    /** @test */
    public function legacy_path_to_storage_preserves_absolute_urls(): void
    {
        $url = 'https://cdn.example.com/logo.png';

        $this->assertSame($url, CmsPathResolver::legacyPathToStorage($url));
    }

    /** @test */
    public function legacy_path_to_storage_normalizes_storage_prefix(): void
    {
        $this->assertSame(
            'cms/carousel/slide.webp',
            CmsPathResolver::legacyPathToStorage('storage/cms/carousel/slide.webp')
        );
    }

    /** @test */
    public function legacy_path_to_storage_maps_carousel_images(): void
    {
        $resolved = CmsPathResolver::legacyPathToStorage('images/carousel/banner.jpg');

        $this->assertStringEndsWith('banner.jpg', $resolved ?? '');
        $this->assertStringContainsString('carousel', $resolved ?? '');
    }

    /** @test */
    public function null_or_empty_returns_null(): void
    {
        $this->assertNull(CmsPathResolver::legacyPathToStorage(null));
        $this->assertNull(CmsPathResolver::legacyPathToStorage(''));
    }
}

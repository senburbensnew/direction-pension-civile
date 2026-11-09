<?php

namespace Tests\Unit\Support;

use App\Support\PublicMedia;
use Tests\TestCase;

class PublicMediaTest extends TestCase
{
    /** @test */
    public function component_src_returns_fallback_for_empty(): void
    {
        $this->assertSame('images/image_placeholder.png', PublicMedia::componentSrc(null));
    }

    /** @test */
    public function component_src_preserves_absolute_urls(): void
    {
        $url = 'https://example.com/logo.png';

        $this->assertSame($url, PublicMedia::componentSrc($url));
    }

    /** @test */
    public function component_src_keeps_legacy_images_paths_as_is(): void
    {
        $this->assertSame(
            'images/carousel/banner.jpg',
            PublicMedia::componentSrc('images/carousel/banner.jpg')
        );
    }

    /** @test */
    public function component_src_prefixes_bare_cms_paths_with_storage(): void
    {
        $this->assertSame(
            'storage/cms/publications/doc.pdf',
            PublicMedia::componentSrc('cms/publications/doc.pdf')
        );
    }

    /** @test */
    public function preferred_url_favors_webp_when_present(): void
    {
        $this->assertSame(
            '/webp/logo.webp',
            PublicMedia::preferredUrl(['url' => '/logo.png', 'webp' => '/webp/logo.webp'])
        );
        $this->assertSame(
            '/logo.png',
            PublicMedia::preferredUrl(['url' => '/logo.png', 'webp' => null])
        );
    }
}

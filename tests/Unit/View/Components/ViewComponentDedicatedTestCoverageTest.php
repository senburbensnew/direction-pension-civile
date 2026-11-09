<?php

namespace Tests\Unit\View\Components;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ViewComponentDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function view_component_catalog_covers_all_components(): void
    {
        $this->assertFileExists(base_path('tests/Unit/View/Components/ViewComponentCatalogTest.php'));

        $catalog = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(ViewComponentCatalogTest::componentProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::viewComponentClasses(), $catalog);
    }
}

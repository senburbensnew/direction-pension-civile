<?php

namespace Tests\Unit\Http\Controllers;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ControllerDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function controller_catalog_covers_all_controllers(): void
    {
        $this->assertFileExists(base_path('tests/Unit/Http/Controllers/ControllerCatalogTest.php'));

        $catalog = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(ControllerCatalogTest::controllerProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::httpControllerClasses(), $catalog);
    }
}

<?php

namespace Tests\Unit\Http\Requests;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class FormRequestDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function form_request_catalog_covers_all_form_requests(): void
    {
        $this->assertFileExists(base_path('tests/Unit/Http/Requests/FormRequestCatalogTest.php'));

        $catalog = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(FormRequestCatalogTest::formRequestProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::formRequestClasses(), $catalog);
    }
}

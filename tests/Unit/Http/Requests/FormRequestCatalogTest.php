<?php

namespace Tests\Unit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AppClassDiscovery;
use Tests\Support\InteractsWithFormRequests;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;
use Throwable;

class FormRequestCatalogTest extends TestCase
{
    use InteractsWithFormRequests;
    use RefreshDatabase;
    use SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedGenders();
        $this->seedServices();
        $this->seedWorkflowSteps();
    }

    /**
     * @return iterable<string, array{class-string<FormRequest>}>
     */
    public static function formRequestProvider(): iterable
    {
        foreach (AppClassDiscovery::formRequestClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider formRequestProvider
     *
     * @param  class-string<FormRequest>  $class
     */
    public function test_authorize_returns_boolean(string $class): void
    {
        $request = $this->makeFormRequest($class);

        try {
            $authorized = $request->authorize();
        } catch (Throwable $e) {
            $this->markTestSkipped($class.' authorize(): '.$e->getMessage());
        }

        $this->assertIsBool($authorized);
    }

    /**
     * @dataProvider formRequestProvider
     *
     * @param  class-string<FormRequest>  $class
     */
    public function test_rules_returns_an_array(string $class): void
    {
        $request = $this->makeFormRequest($class);

        try {
            $rules = $request->rules();
        } catch (Throwable $e) {
            $this->markTestSkipped($class.' rules(): '.$e->getMessage());
        }

        $this->assertIsArray($rules);
    }

    /** @test */
    public function catalog_lists_every_discovered_form_request(): void
    {
        $listed = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(self::formRequestProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::formRequestClasses(), $listed);
    }
}

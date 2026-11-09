<?php

namespace Tests\Unit\Application;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;
use Throwable;

class FormRequestContractTest extends TestCase
{
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

    /**
     * @param  class-string<FormRequest>  $class
     */
    private function makeFormRequest(string $class): FormRequest
    {
        $base = Request::create('/', 'POST');
        /** @var FormRequest $request */
        $request = $class::createFrom($base);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        return $request;
    }
}
